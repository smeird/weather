<?php
// Load shared page components and database connection
include('header.php');
require_once '../dbconn.php';
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.js" type="text/javascript"></script>

<div class="space-y-10">

  <div class="current-conditions-card flex flex-col md:flex-row md:items-center md:justify-between gap-6">
    <div>
      <h1 class="text-4xl font-bold drop-shadow-sm">Current Conditions</h1>
      <p class="mt-3 text-sm max-w-2xl">Live insights from the Wheathampstead personal weather station, refreshed in near real-time for rapid decision making.</p>
    </div>
    <div class="status-pill">
      <i class="fas fa-broadcast-tower"></i>
      <span class="uppercase tracking-[0.35em]">Tap a card to explore the trend</span>

    </div>
  </div>

  <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
    <a href="dynamic-graph.php?WHAT=outTemp&SCALE=day" class="metric-card" style="--accent: 239 68 68; --accent-strong: 220 38 38; --accent-soft: 252 165 165; --accent-glow: 254 226 226;">
      <div class="flex items-start justify-between gap-3">
        <div>
          <span class="metric-label">Outside Temperature</span>
          <div class="metric-value"><span id=OutTemp>-</span> &#176;C</div>
          <p class="metric-meta">Track day and week extremes instantly.</p>
        </div>
        <div class="flex-shrink-0 text-4xl">
          <i class="fas fa-temperature-low"></i>
        </div>
      </div>
    </a>

    <a href="dynamic-graph.php?WHAT=outHumidity&SCALE=day" class="metric-card" style="--accent: 16 185 129; --accent-strong: 5 150 105; --accent-soft: 110 231 183; --accent-glow: 167 243 208;">
      <div class="flex items-start justify-between gap-3">
        <div>
          <span class="metric-label">Outside Humidity</span>
          <div class="metric-value"><span id=OutHumidity>-</span> %</div>
          <p class="metric-meta">Compare moisture swings throughout the day.</p>
        </div>
        <div class="flex-shrink-0 text-4xl">
          <i class="fas fa-tint"></i>
        </div>
      </div>
    </a>

    <a href="dynamic-graph.php?WHAT=windSpeed&SCALE=day" class="metric-card" style="--accent: 14 165 233; --accent-strong: 2 132 199; --accent-soft: 125 211 252; --accent-glow: 191 233 255;">
      <div class="flex items-start justify-between gap-3">
        <div>
          <span class="metric-label">Wind Speed</span>
          <div class="metric-value"><span id=windSpeed_kph>-</span> kph</div>
          <p class="metric-meta">Gauge breezes against historical baselines.</p>
        </div>
        <div class="flex-shrink-0 text-4xl">
          <i class="fas fa-wind"></i>
        </div>
      </div>
    </a>

    <a href="dynamic-graph.php?WHAT=barometer&SCALE=day" class="metric-card" style="--accent: 234 179 8; --accent-strong: 202 138 4; --accent-soft: 253 224 71; --accent-glow: 254 243 199;">
      <div class="flex items-start justify-between gap-3">
        <div>
          <span class="metric-label">Barometer</span>
          <div class="metric-value"><span id=Barometer>-</span> mbar</div>
          <p class="metric-meta">Watch pressure shifts ahead of changing weather.</p>
        </div>
        <div class="flex-shrink-0 text-4xl">
          <i class="fas fa-chart-bar"></i>
        </div>
      </div>
    </a>

    <a href="dynamic-graph.php?WHAT=dewpoint&SCALE=day" class="metric-card" style="--accent: 168 85 247; --accent-strong: 126 34 206; --accent-soft: 196 181 253; --accent-glow: 221 214 254;">
      <div class="flex items-start justify-between gap-3">
        <div>
          <span class="metric-label">Dew Point</span>
          <div class="metric-value"><span id=Dewpoint>-</span> &#176;C</div>
          <p class="metric-meta">Check comfort levels and fog potential.</p>
        </div>
        <div class="flex-shrink-0 text-4xl">
          <i class="fas fa-thermometer-half"></i>
        </div>
      </div>
    </a>

    <a href="dynamic-graph.php?WHAT=windchill&SCALE=day" class="metric-card" style="--accent: 129 140 248; --accent-strong: 99 102 241; --accent-soft: 165 180 252; --accent-glow: 199 210 254;">
      <div class="flex items-start justify-between gap-3">
        <div>
          <span class="metric-label">Wind Chill</span>
          <div class="metric-value"><span id=Windchill>-</span> &#176;C</div>
          <p class="metric-meta">Contrast actual temperature with how it feels.</p>
        </div>
        <div class="flex-shrink-0 text-4xl">
          <i class="fas fa-snowflake"></i>
        </div>
      </div>
    </a>

    <a href="dynamic-graph.php?WHAT=rain&SCALE=day" class="metric-card" style="--accent: 59 130 246; --accent-strong: 37 99 235; --accent-soft: 96 165 250; --accent-glow: 191 219 254;">
      <div class="flex items-start justify-between gap-3">
        <div>
          <span class="metric-label">Rain Today</span>
          <div class="metric-value"><span id=drain>-</span> cm</div>
          <p class="metric-meta">Total rainfall captured since midnight.</p>
        </div>
        <div class="flex-shrink-0 text-4xl">
          <i class="fas fa-cloud-showers-heavy"></i>
        </div>
      </div>
    </a>

    <a href="dynamic-graph.php?WHAT=rain&TYPE=MINMAX&SCALE=month" class="metric-card" style="--accent: 249 115 22; --accent-strong: 217 119 6; --accent-soft: 253 186 116; --accent-glow: 254 215 170;">
      <div class="flex items-start justify-between gap-3">
        <div>
          <span class="metric-label">Rain this Month</span>
          <div class="metric-value"><span id=mrain>-</span> cm</div>
          <p class="metric-meta">Monitor cumulative totals for the month.</p>
        </div>
        <div class="flex-shrink-0 text-4xl">
          <i class="fas fa-umbrella"></i>
        </div>
      </div>
    </a>

    <a href="dynamic-graph.php?WHAT=windGust&SCALE=day" class="metric-card" style="--accent: 56 189 248; --accent-strong: 14 165 233; --accent-soft: 125 211 252; --accent-glow: 224 242 254;">
      <div class="flex items-start justify-between gap-3">
        <div>
          <span class="metric-label">Wind Gust</span>
          <div class="metric-value">
            <span id=windGust_kph>-</span> kph ·
            <span id=windGustDir>-</span>&#176;
          </div>
          <p class="metric-meta">Peak gusts and direction for the past day.</p>
        </div>
        <div class="flex-shrink-0 text-4xl">
          <i class="fas fa-flag"></i>
        </div>
      </div>
    </a>

    <a href="dynamic-graph.php?WHAT=windDir&SCALE=day" class="metric-card" style="--accent: 45 212 191; --accent-strong: 13 148 136; --accent-soft: 94 234 212; --accent-glow: 204 251 241;">
      <div class="flex items-start justify-between gap-3">
        <div>
          <span class="metric-label">Wind Direction</span>
          <div class="metric-value"><span id=windDir>-</span>&#176;</div>
          <p class="metric-meta">Dominant bearing averaged across samples.</p>
        </div>
        <div class="flex-shrink-0 text-4xl">
          <i class="fas fa-compass"></i>
        </div>
      </div>
    </a>
  </div>

  <div class="grid grid-cols-1 gap-6 xl:grid-cols-3 xl:items-stretch">
    <div class="xl:col-span-2">
      <div class="glass-panel h-full">
        <div class="panel-header">
          <h5 class="panel-title">Last 24 Hours</h5>
          <a href="overview-graph.php?FULL=1#graph" class="btn-modern">Full Screen</a>
        </div>
        <div class="panel-body">
          <?php include('overview-graph.php'); ?>
        </div>
      </div>
    </div>
    <div class="xl:col-span-1">
      <div class="glass-panel h-full">
        <div class="panel-header">
          <h5 class="panel-title">Current Garden View</h5>
          <span class="text-[0.6rem] uppercase tracking-[0.35em] text-slate-500 dark:text-slate-300">Live snapshot</span>
        </div>
        <div class="panel-body space-y-4">
          <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">A quick look outside the station to pair the numbers with the current sky.</p>
          <img src="https://www.smeird.com/images/snap.jpeg" class="w-full h-auto rounded-2xl border border-white/40 dark:border-slate-700/60 shadow-lg shadow-slate-900/20 dark:shadow-slate-900/60" alt="Card image">
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
    var connected_flag = 1;
    var mqtt;
    var reconnectTimeout = 1000;
    var host = "mqtt.smeird.com";
    var port = 8083;
    var clean = 0;
    var obj = 0.001;
    var reconnectAttempts = 0;
    var client;

    document.addEventListener('DOMContentLoaded', function() {
      client = new Paho.MQTT.Client(host, port, uuidv4());
      client.onConnectionLost = onConnectionLost;
      client.onMessageArrived = onMessageArrived;
      reconnect();
    });

    function reconnect() {
      var timeout = Math.min(30000, reconnectTimeout * Math.pow(2, reconnectAttempts));
      reconnectAttempts++;
      setStatus('reconnecting');
      setTimeout(function() {
        client.connect({
          useSSL: true,
          onSuccess: onConnect,
          onFailure: onFailure
        });
      }, timeout);
    }

    function uuidv4() {
      return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = Math.random() * 16 | 0,
          v = c == 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
      });
    }

    // called when the client connects
    function onConnect() {
      console.log("onConnect");
      reconnectAttempts = 0;
      client.subscribe("weather/loop");
      setStatus(true);
    }

    function onFailure(responseObject) {
      console.log("onFailure:" + responseObject.errorMessage);
      setStatus(false);
      reconnect();
    }

    // called when the client loses its connection
    function onConnectionLost(responseObject) {
      if (responseObject.errorCode !== 0) {
        console.log("onConnectionLost:" + responseObject.errorMessage);
        setStatus(false);
        reconnect();
      }
    }

    // called when a message arrives
    function onMessageArrived(message) {
      if (message !== null) {
        console.log("onMessageArrived:" + message.payloadString);

        var obj = JSON.parse(message.payloadString);

        document.getElementById("OutTemp").innerHTML = dp(obj.outTemp_C);
        document.getElementById("OutHumidity").innerHTML = dp(obj.outHumidity);
        document.getElementById("windSpeed_kph").innerHTML = dp(obj.windSpeed_kph);
        document.getElementById("windGust_kph").innerHTML = dp(obj.windGust_kph);
        document.getElementById("windDir").innerHTML = dp(obj.windDir);
        document.getElementById("windGustDir").innerHTML = dp(obj.windGustDir);
        document.getElementById("Barometer").innerHTML = dp(obj.pressure_mbar);
        document.getElementById("Dewpoint").innerHTML = dp(obj.dewpoint_C || obj.dewpoint);
        document.getElementById("Windchill").innerHTML = dp(obj.windchill_C || obj.windChill_C || obj.windchill || obj.windChill);
        document.getElementById("drain").innerHTML = dp(obj.dayRain_cm);
        document.getElementById("mrain").innerHTML = dp(obj.monthRain_cm);
      }
    }
    //ll
    function dp(x) {
      return Number.parseFloat(x).toFixed(1);
    }

    function setStatus(status) {
      var el = document.getElementById("connect");
      if (!el) { return; }

      var state = el.querySelector('[data-status-state]');
      var chip = el.querySelector('[data-status-chip]');
      if (!state || !chip) { return; }

      el.classList.remove('status-connected', 'status-reconnecting', 'status-disconnected');

      if (status === true || status === 'connected') {
        el.classList.add('status-connected');
        state.textContent = 'Connected';
        chip.textContent = 'Live';
        chip.setAttribute('aria-label', 'Live connection');
        el.setAttribute('aria-label', 'Connection status: connected');
      } else if (status === 'reconnecting') {
        el.classList.add('status-reconnecting');
        state.textContent = 'Reconnecting';
        chip.textContent = 'Syncing';
        chip.setAttribute('aria-label', 'Attempting to reconnect');
        el.setAttribute('aria-label', 'Connection status: reconnecting');
      } else {
        el.classList.add('status-disconnected');
        state.textContent = 'Disconnected';
        chip.textContent = 'Offline';
        chip.setAttribute('aria-label', 'Offline connection');
        el.setAttribute('aria-label', 'Connection status: disconnected');
      }
    }
  </script>
<?php include('footer.php'); ?>
