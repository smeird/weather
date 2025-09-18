<?php
// Load shared page components and database connection
include('header.php');
require_once '../dbconn.php';
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.js" type="text/javascript"></script>

<div class="dashboard-shell space-y-10">
  <section class="dashboard-hero">
    <div class="hero-grid">
      <div class="hero-copy">
        <span class="hero-chip">Live Weather Dashboard</span>
        <h1 class="text-3xl md:text-4xl font-bold drop-shadow-sm">Weather Intelligence for Wheathampstead</h1>
        <p class="text-sm md:text-base">Synthesised telemetry from the garden station keeps temperature, moisture and wind trends within one glass surface so you can act quickly.</p>
        <div class="status-card status-card-hero status-disconnected" data-status-container role="status" aria-live="polite" aria-label="Connection status: disconnected">
          <span class="status-dot" data-status-dot aria-hidden="true"></span>
          <div class="status-copy">
            <span class="status-label">Station Link</span>
            <span class="status-state" data-status-state>Disconnected</span>
          </div>
          <span class="status-chip" data-status-chip aria-label="Offline connection">Offline</span>
        </div>
      </div>
      <div class="hero-stats-grid">
        <div class="insight-card hero-quick-stats">
          <div class="flex items-baseline justify-between">
            <span class="text-xs uppercase tracking-[0.3em] text-slate-600 dark:text-slate-300">Quick Stats</span>
            <i class="fas fa-layer-group text-slate-400 dark:text-slate-500"></i>
          </div>
          <ul class="insight-list">
            <li>
              <span class="label">Rain Today</span>
              <span class="stat-reading">
                <span data-stat="drain">--</span>
                <span class="stat-unit">cm</span>
              </span>
            </li>
            <li>
              <span class="label">Rain This Month</span>
              <span class="stat-reading">
                <span data-stat="mrain">--</span>
                <span class="stat-unit">cm</span>
              </span>
            </li>
            <li>
              <span class="label">Peak Gust</span>
              <span class="stat-reading">
                <span data-stat="windGust_kph">--</span>
                <span class="stat-unit">kph · Dir</span>
                <span data-stat="windGustDir">--</span>
                <span class="stat-unit">°</span>
              </span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="metric-section space-y-6">
    <div class="section-header">
      <div class="space-y-2">
        <h2 class="text-xl md:text-2xl font-semibold">Interactive Metrics</h2>
        <p>Transparent cards keep the focus on the numbers while providing quick access to the full Highcharts history for each sensor.</p>
      </div>
      <span class="section-chip">
        <i class="fas fa-broadcast-tower"></i>
        Live Feed
      </span>
    </div>
    <div class="metric-grid grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
      <a href="dynamic-graph.php?WHAT=outTemp&SCALE=day" class="metric-card" style="--accent: 239 68 68; --accent-strong: 220 38 38; --accent-soft: 252 165 165;">
        <div class="flex items-start justify-between gap-3">
          <div>
            <span class="metric-label">Outside Temperature</span>
            <div class="metric-value"><span data-stat="OutTemp">--</span><span class="stat-unit">°C</span></div>
            <p class="metric-meta">Track rising and falling periods across the day.</p>
          </div>
          <div class="flex-shrink-0 text-3xl">
            <i class="fas fa-temperature-low"></i>
          </div>
        </div>
      </a>

      <a href="dynamic-graph.php?WHAT=outHumidity&SCALE=day" class="metric-card" style="--accent: 16 185 129; --accent-strong: 5 150 105; --accent-soft: 110 231 183;">
        <div class="flex items-start justify-between gap-3">
          <div>
            <span class="metric-label">Outside Humidity</span>
            <div class="metric-value"><span data-stat="OutHumidity">--</span><span class="stat-unit">%</span></div>
            <p class="metric-meta">Balance dew point and ambient readings instantly.</p>
          </div>
          <div class="flex-shrink-0 text-3xl">
            <i class="fas fa-tint"></i>
          </div>
        </div>
      </a>

      <a href="dynamic-graph.php?WHAT=windSpeed&SCALE=day" class="metric-card" style="--accent: 14 165 233; --accent-strong: 2 132 199; --accent-soft: 125 211 252;">
        <div class="flex items-start justify-between gap-3">
          <div>
            <span class="metric-label">Wind Speed</span>
            <div class="metric-value"><span data-stat="windSpeed_kph">--</span><span class="stat-unit">kph</span></div>
            <p class="metric-meta">Compare breezes to the 24 hour envelope.</p>
          </div>
          <div class="flex-shrink-0 text-3xl">
            <i class="fas fa-wind"></i>
          </div>
        </div>
      </a>

      <a href="dynamic-graph.php?WHAT=barometer&SCALE=day" class="metric-card" style="--accent: 234 179 8; --accent-strong: 202 138 4; --accent-soft: 253 224 71;">
        <div class="flex items-start justify-between gap-3">
          <div>
            <span class="metric-label">Barometer</span>
            <div class="metric-value"><span data-stat="Barometer">--</span><span class="stat-unit">mbar</span></div>
            <p class="metric-meta">Watch pressure shifts for synoptic changes.</p>
          </div>
          <div class="flex-shrink-0 text-3xl">
            <i class="fas fa-chart-bar"></i>
          </div>
        </div>
      </a>

      <a href="dynamic-graph.php?WHAT=dewpoint&SCALE=day" class="metric-card" style="--accent: 168 85 247; --accent-strong: 126 34 206; --accent-soft: 196 181 253;">
        <div class="flex items-start justify-between gap-3">
          <div>
            <span class="metric-label">Dew Point</span>
            <div class="metric-value"><span data-stat="Dewpoint">--</span><span class="stat-unit">°C</span></div>
            <p class="metric-meta">Assess comfort levels with saturation insight.</p>
          </div>
          <div class="flex-shrink-0 text-3xl">
            <i class="fas fa-thermometer-half"></i>
          </div>
        </div>
      </a>

      <a href="dynamic-graph.php?WHAT=windchill&SCALE=day" class="metric-card" style="--accent: 129 140 248; --accent-strong: 99 102 241; --accent-soft: 165 180 252;">
        <div class="flex items-start justify-between gap-3">
          <div>
            <span class="metric-label">Wind Chill</span>
            <div class="metric-value"><span data-stat="Windchill">--</span><span class="stat-unit">°C</span></div>
            <p class="metric-meta">Contrast actual temperature with exposure.</p>
          </div>
          <div class="flex-shrink-0 text-3xl">
            <i class="fas fa-snowflake"></i>
          </div>
        </div>
      </a>

      <a href="dynamic-graph.php?WHAT=rain&SCALE=day" class="metric-card" style="--accent: 59 130 246; --accent-strong: 37 99 235; --accent-soft: 96 165 250;">
        <div class="flex items-start justify-between gap-3">
          <div>
            <span class="metric-label">Rain Today</span>
            <div class="metric-value"><span data-stat="drain">--</span><span class="stat-unit">cm</span></div>
            <p class="metric-meta">Total captured since midnight.</p>
          </div>
          <div class="flex-shrink-0 text-3xl">
            <i class="fas fa-cloud-showers-heavy"></i>
          </div>
        </div>
      </a>

      <a href="dynamic-graph.php?WHAT=rain&TYPE=MINMAX&SCALE=month" class="metric-card" style="--accent: 249 115 22; --accent-strong: 217 119 6; --accent-soft: 253 186 116;">
        <div class="flex items-start justify-between gap-3">
          <div>
            <span class="metric-label">Rain This Month</span>
            <div class="metric-value"><span data-stat="mrain">--</span><span class="stat-unit">cm</span></div>
            <p class="metric-meta">Monitor cumulative totals for the month.</p>
          </div>
          <div class="flex-shrink-0 text-3xl">
            <i class="fas fa-umbrella"></i>
          </div>
        </div>
      </a>

      <a href="dynamic-graph.php?WHAT=windGust&SCALE=day" class="metric-card" style="--accent: 56 189 248; --accent-strong: 14 165 233; --accent-soft: 125 211 252;">
        <div class="flex items-start justify-between gap-3">
          <div>
            <span class="metric-label">Wind Gust</span>
            <div class="metric-value">
              <span data-stat="windGust_kph">--</span><span class="stat-unit">kph</span>
            </div>
            <p class="metric-meta">Peak direction <span data-stat="windGustDir">--</span>°</p>
          </div>
          <div class="flex-shrink-0 text-3xl">
            <i class="fas fa-flag"></i>
          </div>
        </div>
      </a>

      <a href="dynamic-graph.php?WHAT=windDir&SCALE=day" class="metric-card" style="--accent: 45 212 191; --accent-strong: 13 148 136; --accent-soft: 94 234 212;">
        <div class="flex items-start justify-between gap-3">
          <div>
            <span class="metric-label">Wind Direction</span>
            <div class="metric-value"><span data-stat="windDir">--</span><span class="stat-unit">°</span></div>
            <p class="metric-meta">Dominant bearing across the loop.</p>
          </div>
          <div class="flex-shrink-0 text-3xl">
            <i class="fas fa-compass"></i>
          </div>
        </div>
      </a>
    </div>
  </section>

  <div class="panels-grid">
    <div class="glass-panel h-full">
      <div class="panel-header">
        <h5 class="panel-title">Last 24 Hours</h5>
        <a href="overview-graph.php?FULL=1#graph" class="btn-modern">Full Screen</a>
      </div>
      <div class="panel-body">
        <?php include('overview-graph.php'); ?>
      </div>
    </div>
    <div class="space-y-6">
      <div class="glass-panel h-full">
        <div class="panel-header">
          <h5 class="panel-title">Current Garden View</h5>
          <span class="text-[0.6rem] uppercase tracking-[0.35em] text-slate-500 dark:text-slate-300">Live snapshot</span>
        </div>
        <div class="panel-body space-y-4">
          <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">Pair the data stream with the immediate view outside the station.</p>
          <img src="https://www.smeird.com/images/snap.jpeg" class="w-full h-auto rounded-2xl border border-white/40 dark:border-slate-700/60 shadow-lg shadow-slate-900/20 dark:shadow-slate-900/60" alt="Station garden snapshot">
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

    function onConnectionLost(responseObject) {
      if (responseObject.errorCode !== 0) {
        console.log("onConnectionLost:" + responseObject.errorMessage);
        setStatus(false);
        reconnect();
      }
    }

    function onMessageArrived(message) {
      if (message !== null) {
        console.log("onMessageArrived:" + message.payloadString);
        var obj = JSON.parse(message.payloadString);
        setStat('OutTemp', dp(obj.outTemp_C));
        setStat('OutHumidity', dp(obj.outHumidity));
        setStat('windSpeed_kph', dp(obj.windSpeed_kph));
        setStat('windGust_kph', dp(obj.windGust_kph));
        setStat('windDir', dp(obj.windDir));
        setStat('windGustDir', dp(obj.windGustDir));
        setStat('Barometer', dp(obj.pressure_mbar));
        setStat('Dewpoint', dp(obj.dewpoint_C || obj.dewpoint));
        setStat('Windchill', dp(obj.windchill_C || obj.windChill_C || obj.windchill || obj.windChill));
        setStat('drain', dp(obj.dayRain_cm));
        setStat('mrain', dp(obj.monthRain_cm));
      }
    }

    function setStat(stat, value) {
      document.querySelectorAll('[data-stat="' + stat + '"]').forEach(function(node) {
        node.textContent = value;
      });
    }

    function dp(x) {
      var num = Number.parseFloat(x);
      if (Number.isFinite(num)) {
        return num.toFixed(1);
      }
      return '--';
    }

    function setStatus(status) {
      var containers = document.querySelectorAll('[data-status-container]');
      containers.forEach(function(el) {
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
      });
    }
  </script>
<?php include('footer.php'); ?>
