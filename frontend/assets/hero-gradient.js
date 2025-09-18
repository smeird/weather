/*! hero-gradient.js: set window.SMEIRD before load (broker/creds/topics/coords/timers); requires mqtt.min.js global `mqtt` and hero-gradient.css; URL params hero_override=STATE (30m) & hero_debug=1. */
(function () {
  var win = window;
  var doc = document;
  if (!doc || typeof mqtt === 'undefined') return;
  var topics = {
    outside_temp: 'sensors/outside/temperature_c',
    wind_speed: 'sensors/wind/speed_kmh',
    barometer: 'sensors/weather/pressure_hpa',
    dew_point: 'sensors/outside/dewpoint_c',
    wind_chill: 'sensors/outside/windchill_c',
    rain_today: 'sensors/rain/today_mm',
    wind_gust: 'sensors/wind/gust_kmh',
    wind_dir: 'sensors/wind/dir_deg'
  };
  var defaults = {
    brokerUrl: 'wss://mqtt.example.com:8083/mqtt',
    clientId: 'smeird-hero-' + Math.random().toString(16).slice(2),
    username: null,
    password: null,
    topics: topics,
    siteLat: 51.8117,
    siteLon: -0.2939,
    staleAfterMs: 600000,
    minDwellMs: 300000,
    evalIntervalMs: 60000
  };
  var user = win.SMEIRD || {};
  var SMEIRD = win.SMEIRD = Object.assign({}, defaults, user);
  SMEIRD.topics = Object.assign({}, topics, user.topics || {});
  var required = ['outside_temp', 'wind_speed', 'barometer', 'dew_point', 'wind_gust', 'rain_today'];
  var ranges = {
    outside_temp: [-60, 60], dew_point: [-60, 60],
    wind_speed: [0, 200], wind_gust: [0, 250],
    barometer: [800, 1100], wind_chill: [-80, 60],
    rain_today: [0, 2000]
  };
  var severity = { stale: 9, storm: 8, 'heavy-rain': 7, rain: 6, snow: 5, fog: 4, windy: 3, hot: 2, cold: 2, 'clear-night': 1, overcast: 1 };
  var vals = {};
  var lastMsg = 0;
  var rainHist = [];
  var pressureHist = [];
  var currentState = null;
  var lastChange = 0;
  var overrideState = null;
  var overrideUntil = 0;
  var params = new URLSearchParams(win.location.search);
  var debugMode = params.get('hero_debug') === '1';
  var panel = null;

  if (params.get('hero_override')) {
    setOverride(params.get('hero_override'), Date.now() + 1800000);
  } else {
    readOverride();
  }

  if (debugMode) {
    panel = createPanel();
  }

  var client = mqtt.connect(SMEIRD.brokerUrl, {
    clientId: SMEIRD.clientId,
    username: SMEIRD.username || undefined,
    password: SMEIRD.password || undefined,
    reconnectPeriod: 5000
  });

  client.on('connect', function () {
    Object.keys(SMEIRD.topics).forEach(function (key) {
      if (SMEIRD.topics[key]) {
        client.subscribe(SMEIRD.topics[key]);
      }
    });
  });

  client.on('message', function (topic, payload) {
    var key = getKey(topic);
    if (!key) return;
    var val = parseNumber(key, payload);
    if (val === null) return;
    vals[key] = val;
    lastMsg = Date.now();
    if (debugMode) updatePanel();
  });

  setInterval(evaluate, SMEIRD.evalIntervalMs);
  evaluate();

  function getKey(topic) {
    for (var k in SMEIRD.topics) {
      if (SMEIRD.topics[k] === topic) return k;
    }
    return null;
  }

  function parseNumber(key, payload) {
    var str;
    if (payload instanceof Uint8Array) {
      if (typeof TextDecoder !== 'undefined') {
        str = new TextDecoder().decode(payload);
      } else {
        str = String.fromCharCode.apply(null, payload);
      }
    } else {
      str = String(payload);
    }
    var num = parseFloat(str);
    if (!isFinite(num)) return null;
    var range = ranges[key];
    if (range) {
      if (num < range[0]) num = range[0];
      if (num > range[1]) num = range[1];
    }
    return num;
  }

  function haveAll() {
    for (var i = 0; i < required.length; i++) {
      if (!(required[i] in vals)) return false;
    }
    return true;
  }

  function maintainHistory(now) {
    rainHist.push({ ts: now, value: vals.rain_today });
    pressureHist.push({ ts: now, value: vals.barometer });
    var rainCut = now - 900000;
    while (rainHist.length && rainHist[0].ts < rainCut) rainHist.shift();
    var pressureCut = now - 3600000;
    while (pressureHist.length && pressureHist[0].ts < pressureCut) pressureHist.shift();
  }

  function rainDelta(ms, now) {
    if (!rainHist.length) return 0;
    var latest = rainHist[rainHist.length - 1];
    var base = rainHist[0];
    for (var i = rainHist.length - 1; i >= 0; i--) {
      if (now - rainHist[i].ts <= ms) base = rainHist[i];
    }
    var delta = latest.value - base.value;
    return delta > 0 ? delta : 0;
  }

  function pressureDrop(ms, now) {
    if (!pressureHist.length) return 0;
    var latest = pressureHist[pressureHist.length - 1];
    var ref = pressureHist[0];
    for (var i = pressureHist.length - 1; i >= 0; i--) {
      if (now - pressureHist[i].ts >= ms) {
        ref = pressureHist[i];
        break;
      }
    }
    var drop = ref.value - latest.value;
    return drop > 0 ? drop : 0;
  }

  function evaluate() {
    var now = Date.now();
    if (overrideState && now > overrideUntil) clearOverride();
    if (!haveAll()) return;
    maintainHistory(now);
    var v = vals;
    var rain15 = rainDelta(900000, now);
    var drop60 = pressureDrop(3600000, now);
    var night = sunElevation(now, SMEIRD.siteLat, SMEIRD.siteLon) < -6;
    var result = computeState(now, rain15, drop60, night);
    var target = result.state;
    var cause = result.cause;
    if (overrideState) {
      target = overrideState;
      cause = 'override';
    }
    if (target !== currentState) {
      if (currentState && now - lastChange < SMEIRD.minDwellMs && severity[target] <= severity[currentState]) {
        target = currentState;
        cause = 'dwell';
      } else if (currentState === 'windy' && target !== 'windy' && severity[target] < severity[currentState] && ((v.wind_speed || 0) >= 25 || (v.wind_gust || 0) >= 35)) {
        target = currentState;
        cause = 'windy-hold';
      } else if (currentState === 'hot' && target !== 'hot' && severity[target] < severity[currentState] && (v.outside_temp || 0) >= 26) {
        target = currentState;
        cause = 'hot-hold';
      } else if (currentState === 'cold' && target !== 'cold' && severity[target] < severity[currentState] && !((v.outside_temp || 0) > 3 && (v.wind_chill === undefined || v.wind_chill > 1))) {
        target = currentState;
        cause = 'cold-hold';
      }
    }
    var info = {
      state: target,
      cause: cause,
      isNight: night,
      lastChangeTs: lastChange,
      rain15: rain15,
      pressureDrop: drop60,
      values: Object.assign({}, v)
    };
    if (target !== currentState) {
      currentState = target;
      lastChange = now;
      info.lastChangeTs = now;
      applyState(target, cause, info);
    }
    win.__smeirdHeroState = info;
    if (debugMode) updatePanel();
  }

  function computeState(now, rain15, drop60, night) {
    var v = vals;
    if (!lastMsg || now - lastMsg > SMEIRD.staleAfterMs) {
      return { state: 'stale', cause: 'stale' };
    }
    if ((v.wind_gust || 0) >= 50 || drop60 >= 2) {
      return { state: 'storm', cause: 'gust/pressure' };
    }
    if (rain15 >= 0.5) {
      return { state: 'heavy-rain', cause: 'rain15=' + rain15.toFixed(2) };
    }
    if (rain15 > 0) {
      return { state: 'rain', cause: 'rain15=' + rain15.toFixed(2) };
    }
    if (((v.outside_temp || 0) <= 0 && rain15 > 0) || (v.wind_chill !== undefined && v.wind_chill <= -1)) {
      return { state: 'snow', cause: 'temp/windchill' };
    }
    if (Math.abs((v.outside_temp || 0) - (v.dew_point || 0)) <= 1.5 && (v.wind_speed || 0) < 3) {
      return { state: 'fog', cause: 'dewpoint proximity' };
    }
    if ((v.wind_speed || 0) >= 30 || (v.wind_gust || 0) >= 40) {
      return { state: 'windy', cause: 'wind' };
    }
    if ((v.outside_temp || 0) >= 28) {
      return { state: 'hot', cause: 'temp' };
    }
    if ((v.outside_temp || 0) <= 2 || (v.wind_chill !== undefined && v.wind_chill <= 0)) {
      return { state: 'cold', cause: 'temp' };
    }
    if (night && rain15 === 0 && drop60 < 2 && (v.wind_gust || 0) < 50) {
      return { state: 'clear-night', cause: 'night' };
    }
    return { state: 'overcast', cause: 'default' };
  }

  function applyState(state, cause, info) {
    if (doc.body) doc.body.setAttribute('data-weather', state);
    console.info('hero state %s (%s)', state, cause, info);
  }

  function setOverride(state, expiry) {
    if (!severity.hasOwnProperty(state)) return;
    overrideState = state;
    overrideUntil = expiry;
    try {
      localStorage.setItem('smeirdHeroOverride', JSON.stringify({ state: state, expires: expiry }));
    } catch (e) {}
  }

  function clearOverride() {
    overrideState = null;
    overrideUntil = 0;
    try {
      localStorage.removeItem('smeirdHeroOverride');
    } catch (e) {}
  }

  function readOverride() {
    try {
      var raw = localStorage.getItem('smeirdHeroOverride');
      if (!raw) return;
      var data = JSON.parse(raw);
      if (data && data.state && data.expires > Date.now()) {
        overrideState = data.state;
        overrideUntil = data.expires;
      } else {
        localStorage.removeItem('smeirdHeroOverride');
      }
    } catch (e) {}
  }

  function sunElevation(ms, lat, lon) {
    var rad = Math.PI / 180;
    var d = (ms - Date.UTC(2000, 0, 1, 12)) / 86400000;
    var g = 357.529 + 0.98560028 * d;
    var q = 280.459 + 0.98564736 * d;
    var gR = g * rad;
    var L = (q + 1.915 * Math.sin(gR) + 0.02 * Math.sin(2 * gR)) % 360;
    if (L < 0) L += 360;
    var Lr = L * rad;
    var e = (23.439 - 0.00000036 * d) * rad;
    var RA = Math.atan2(Math.cos(e) * Math.sin(Lr), Math.cos(Lr));
    var RAdeg = (RA / rad + 360) % 360;
    var eq = q % 360 - RAdeg;
    if (eq > 180) eq -= 360;
    if (eq < -180) eq += 360;
    var eqMin = 4 * eq;
    var date = new Date(ms);
    var utcMin = date.getUTCHours() * 60 + date.getUTCMinutes() + date.getUTCSeconds() / 60;
    var tst = (utcMin + eqMin + lon * 4 + 1440) % 1440;
    var ha = tst / 4 - 180;
    var haR = ha * rad;
    var latR = lat * rad;
    var dec = Math.asin(Math.sin(e) * Math.sin(Lr));
    var elev = Math.asin(Math.sin(latR) * Math.sin(dec) + Math.cos(latR) * Math.cos(dec) * Math.cos(haR));
    return elev / rad;
  }

  function createPanel() {
    var wrap = doc.createElement('div');
    wrap.style.cssText = 'position:fixed;bottom:1rem;right:1rem;z-index:9999;background:rgba(0,0,0,.78);color:#fff;padding:.75rem;border-radius:8px;font:12px/1.4 sans-serif;max-width:260px;';
    wrap.innerHTML = '<strong>Hero Debug</strong><div class="hero-debug-values"></div><div class="hero-debug-buttons" style="margin-top:.5rem"></div>';
    doc.body.appendChild(wrap);
    var buttons = wrap.querySelector('.hero-debug-buttons');
    ['storm', 'heavy-rain', 'rain', 'snow', 'fog', 'windy', 'hot', 'cold'].forEach(function (s) {
      var b = doc.createElement('button');
      b.type = 'button';
      b.textContent = s;
      b.style.cssText = 'margin:0 .25rem .25rem 0;padding:.25rem .5rem;font-size:11px;';
      b.addEventListener('click', function () { simulate(s); });
      buttons.appendChild(b);
    });
    var clearBtn = doc.createElement('button');
    clearBtn.type = 'button';
    clearBtn.textContent = 'clear override';
    clearBtn.style.cssText = 'margin:.25rem 0 0;padding:.25rem .5rem;font-size:11px;';
    clearBtn.addEventListener('click', function () { clearOverride(); evaluate(); });
    buttons.appendChild(clearBtn);
    return wrap;
  }

  function simulate(state) {
    required.forEach(function (key) {
      if (!(key in vals)) {
        vals[key] = key === 'outside_temp' ? 10 : key === 'barometer' ? 1013 : 0;
      }
    });
    if (!('dew_point' in vals)) vals.dew_point = vals.outside_temp - 2;
    if (!('wind_chill' in vals)) vals.wind_chill = vals.outside_temp;
    if (!('wind_gust' in vals)) vals.wind_gust = vals.wind_speed;
    var now = Date.now();
    switch (state) {
      case 'storm':
        vals.wind_gust = 60;
        vals.wind_speed = 35;
        pressureHist.push({ ts: now - 3600000, value: (vals.barometer || 1010) + 3 });
        break;
      case 'heavy-rain':
        rainHist.push({ ts: now - 900000, value: vals.rain_today || 0 });
        vals.rain_today = (vals.rain_today || 0) + 1;
        break;
      case 'rain':
        rainHist.push({ ts: now - 900000, value: vals.rain_today || 0 });
        vals.rain_today = (vals.rain_today || 0) + 0.2;
        break;
      case 'snow':
        vals.outside_temp = -1;
        vals.wind_chill = -2;
        break;
      case 'fog':
        vals.dew_point = vals.outside_temp;
        vals.wind_speed = 1;
        break;
      case 'windy':
        vals.wind_speed = 32;
        vals.wind_gust = 45;
        break;
      case 'hot':
        vals.outside_temp = 30;
        break;
      case 'cold':
        vals.outside_temp = 0;
        vals.wind_chill = -1;
        break;
    }
    evaluate();
  }

  function updatePanel() {
    if (!panel) return;
    var target = panel.querySelector('.hero-debug-values');
    var info = win.__smeirdHeroState || {};
    var lines = [
      'state: ' + (info.state || 'n/a'),
      'cause: ' + (info.cause || 'n/a'),
      'night: ' + (info.isNight ? 'yes' : 'no')
    ];
    Object.keys(vals).forEach(function (k) { lines.push(k + ': ' + vals[k]); });
    target.innerHTML = '<pre style="margin:0">' + lines.join('\n') + '</pre>';
  }
})();
