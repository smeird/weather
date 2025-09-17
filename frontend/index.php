<?php
// Load shared page components and database connection
include('header.php');
require_once '../dbconn.php';
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.js" type="text/javascript"></script>

<div class="space-y-8">
  <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div>
      <h1 class="text-3xl font-semibold tracking-tight text-slate-900 dark:text-slate-100 drop-shadow-sm">Live Weather Snapshot</h1>
      <p class="mt-1 text-sm sm:text-base text-slate-600 dark:text-slate-300">Detailed conditions from Wheathampstead updated every few seconds.</p>
    </div>
    <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
      <i class="fas fa-location-dot text-blue-400"></i>
      <div class="flex flex-col text-left">
        <span class="text-[0.65rem] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Station</span>
        <span>Wheathampstead, UK &middot; <?php echo date('j M Y H:i'); ?></span>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
    <div class="relative overflow-hidden glass-card border-l-4 border-red-500 rounded-2xl shadow-xl">
      <a href="dynamic-graph.php?WHAT=outTemp&SCALE=day" class="block group focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-red-400/60 focus-visible:ring-offset-transparent rounded-2xl">
        <span class="absolute -top-20 -right-16 h-40 w-40 bg-red-400/25 blur-3xl transition-transform duration-500 group-hover:scale-105"></span>
        <div class="relative flex items-center justify-between gap-4">
          <div class="flex flex-col gap-2">
            <span class="text-[0.65rem] font-semibold uppercase tracking-[0.22em] text-red-500/80">Outside Temperature</span>
            <span class="text-3xl font-semibold text-slate-900 dark:text-slate-100 drop-shadow-sm"><span id="OutTemp">-</span> &#176;C</span>
            <span class="text-xs text-slate-600/80 dark:text-slate-300/80">Tap for today's trend</span>
          </div>
          <div class="text-red-400/60 transition-colors duration-300 group-hover:text-red-300">
            <i class="fas fa-temperature-low fa-2x"></i>
          </div>
        </div>
      </a>
    </div>
    <div class="relative overflow-hidden glass-card border-l-4 border-emerald-500 rounded-2xl shadow-xl">
      <a href="dynamic-graph.php?WHAT=outHumidity&SCALE=day" class="block group focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-emerald-400/60 focus-visible:ring-offset-transparent rounded-2xl">
        <span class="absolute -top-16 -right-12 h-36 w-36 bg-emerald-400/25 blur-3xl transition-transform duration-500 group-hover:scale-105"></span>
        <div class="relative flex items-center justify-between gap-4">
          <div class="flex flex-col gap-2">
            <span class="text-[0.65rem] font-semibold uppercase tracking-[0.22em] text-emerald-500/80">Outside Humidity</span>
            <span class="text-3xl font-semibold text-slate-900 dark:text-slate-100 drop-shadow-sm"><span id="OutHumidity">-</span> %</span>
            <span class="text-xs text-slate-600/80 dark:text-slate-300/80">Tap for humidity trends</span>
          </div>
          <div class="text-emerald-400/70 transition-colors duration-300 group-hover:text-emerald-300">
            <i class="fas fa-droplet fa-2x"></i>
          </div>
        </div>
      </a>
    </div>
    <div class="relative overflow-hidden glass-card border-l-4 border-cyan-500 rounded-2xl shadow-xl">
      <a href="dynamic-graph.php?WHAT=windSpeed&SCALE=day" class="block group focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-cyan-400/60 focus-visible:ring-offset-transparent rounded-2xl">
        <span class="absolute -top-20 -right-16 h-40 w-40 bg-cyan-400/25 blur-3xl transition-transform duration-500 group-hover:scale-105"></span>
        <div class="relative flex items-center justify-between gap-4">
          <div class="flex flex-col gap-2">
            <span class="text-[0.65rem] font-semibold uppercase tracking-[0.22em] text-cyan-500/80">Wind Speed</span>
            <span class="text-3xl font-semibold text-slate-900 dark:text-slate-100 drop-shadow-sm"><span id="windSpeed_kph">-</span> kph</span>
            <span class="text-xs text-slate-600/80 dark:text-slate-300/80">Tap for wind profile</span>
          </div>
          <div class="text-cyan-400/70 transition-colors duration-300 group-hover:text-cyan-300">
            <i class="fas fa-wind fa-2x"></i>
          </div>
        </div>
      </a>
    </div>
    <div class="relative overflow-hidden glass-card border-l-4 border-amber-500 rounded-2xl shadow-xl">
      <a href="dynamic-graph.php?WHAT=barometer&SCALE=day" class="block group focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-amber-400/60 focus-visible:ring-offset-transparent rounded-2xl">
        <span class="absolute -top-20 -right-16 h-40 w-40 bg-amber-400/25 blur-3xl transition-transform duration-500 group-hover:scale-105"></span>
        <div class="relative flex items-center justify-between gap-4">
          <div class="flex flex-col gap-2">
            <span class="text-[0.65rem] font-semibold uppercase tracking-[0.22em] text-amber-500/80">Barometer</span>
            <span class="text-3xl font-semibold text-slate-900 dark:text-slate-100 drop-shadow-sm"><span id="Barometer">-</span> mbar</span>
            <span class="text-xs text-slate-600/80 dark:text-slate-300/80">Tap for pressure shifts</span>
          </div>
          <div class="text-amber-400/70 transition-colors duration-300 group-hover:text-amber-300">
            <i class="fas fa-gauge-high fa-2x"></i>
          </div>
        </div>
      </a>
    </div>
    <div class="relative overflow-hidden glass-card border-l-4 border-purple-500 rounded-2xl shadow-xl">
      <a href="dynamic-graph.php?WHAT=dewpoint&SCALE=day" class="block group focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-purple-400/60 focus-visible:ring-offset-transparent rounded-2xl">
        <span class="absolute -top-16 -right-14 h-36 w-36 bg-purple-400/25 blur-3xl transition-transform duration-500 group-hover:scale-105"></span>
        <div class="relative flex items-center justify-between gap-4">
          <div class="flex flex-col gap-2">
            <span class="text-[0.65rem] font-semibold uppercase tracking-[0.22em] text-purple-500/80">Dew Point</span>
            <span class="text-3xl font-semibold text-slate-900 dark:text-slate-100 drop-shadow-sm"><span id="Dewpoint">-</span> &#176;C</span>
            <span class="text-xs text-slate-600/80 dark:text-slate-300/80">Tap for dew point trace</span>
          </div>
          <div class="text-purple-400/70 transition-colors duration-300 group-hover:text-purple-300">
            <i class="fas fa-thermometer-half fa-2x"></i>
          </div>
        </div>
      </a>
    </div>
    <div class="relative overflow-hidden glass-card border-l-4 border-indigo-500 rounded-2xl shadow-xl">
      <a href="dynamic-graph.php?WHAT=windchill&SCALE=day" class="block group focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-indigo-400/60 focus-visible:ring-offset-transparent rounded-2xl">
        <span class="absolute -top-16 -right-14 h-36 w-36 bg-indigo-400/20 blur-3xl transition-transform duration-500 group-hover:scale-105"></span>
        <div class="relative flex items-center justify-between gap-4">
          <div class="flex flex-col gap-2">
            <span class="text-[0.65rem] font-semibold uppercase tracking-[0.22em] text-indigo-500/80">Wind Chill</span>
            <span class="text-3xl font-semibold text-slate-900 dark:text-slate-100 drop-shadow-sm"><span id="Windchill">-</span> &#176;C</span>
            <span class="text-xs text-slate-600/80 dark:text-slate-300/80">Tap for comfort index</span>
          </div>
          <div class="text-indigo-400/70 transition-colors duration-300 group-hover:text-indigo-300">
            <i class="fas fa-snowflake fa-2x"></i>
          </div>
        </div>
      </a>
    </div>
    <div class="relative overflow-hidden glass-card border-l-4 border-sky-500 rounded-2xl shadow-xl">
      <a href="dynamic-graph.php?WHAT=rain&SCALE=day" class="block group focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-sky-400/60 focus-visible:ring-offset-transparent rounded-2xl">
        <span class="absolute -top-20 -right-14 h-40 w-40 bg-sky-400/25 blur-3xl transition-transform duration-500 group-hover:scale-105"></span>
        <div class="relative flex items-center justify-between gap-4">
          <div class="flex flex-col gap-2">
            <span class="text-[0.65rem] font-semibold uppercase tracking-[0.22em] text-sky-500/80">Rain Today</span>
            <span class="text-3xl font-semibold text-slate-900 dark:text-slate-100 drop-shadow-sm"><span id="drain">-</span> cm</span>
            <span class="text-xs text-slate-600/80 dark:text-slate-300/80">Tap for rainfall totals</span>
          </div>
          <div class="text-sky-400/70 transition-colors duration-300 group-hover:text-sky-300">
            <i class="fas fa-cloud-rain fa-2x"></i>
          </div>
        </div>
      </a>
    </div>
    <div class="relative overflow-hidden glass-card border-l-4 border-sky-600 rounded-2xl shadow-xl">
      <a href="dynamic-graph.php?WHAT=rain&TYPE=MINMAX&SCALE=month" class="block group focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-sky-500/60 focus-visible:ring-offset-transparent rounded-2xl">
        <span class="absolute -top-16 -right-12 h-36 w-36 bg-sky-500/25 blur-3xl transition-transform duration-500 group-hover:scale-105"></span>
        <div class="relative flex items-center justify-between gap-4">
          <div class="flex flex-col gap-2">
            <span class="text-[0.65rem] font-semibold uppercase tracking-[0.22em] text-sky-600/80">Rain This Month</span>
            <span class="text-3xl font-semibold text-slate-900 dark:text-slate-100 drop-shadow-sm"><span id="mrain">-</span> cm</span>
            <span class="text-xs text-slate-600/80 dark:text-slate-300/80">Tap for monthly totals</span>
          </div>
          <div class="text-sky-500/70 transition-colors duration-300 group-hover:text-sky-300">
            <i class="fas fa-calendar-days fa-2x"></i>
          </div>
        </div>
      </a>
    </div>
    <div class="relative overflow-hidden glass-card border-l-4 border-cyan-500 rounded-2xl shadow-xl">
      <a href="dynamic-graph.php?WHAT=windGust&SCALE=day" class="block group focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-cyan-400/60 focus-visible:ring-offset-transparent rounded-2xl">
        <span class="absolute -top-16 -right-14 h-36 w-36 bg-cyan-400/25 blur-3xl transition-transform duration-500 group-hover:scale-105"></span>
        <div class="relative flex items-center justify-between gap-4">
          <div class="flex flex-col gap-2">
            <span class="text-[0.65rem] font-semibold uppercase tracking-[0.22em] text-cyan-500/80">Wind Gust</span>
            <span class="text-3xl font-semibold text-slate-900 dark:text-slate-100 drop-shadow-sm"><span id="windGust_kph">-</span> kph</span>
            <span class="text-xs text-slate-600/80 dark:text-slate-300/80">Direction <span id="windGustDir">-</span>&#176;</span>
          </div>
          <div class="text-cyan-400/70 transition-colors duration-300 group-hover:text-cyan-300">
            <i class="fas fa-wind fa-2x"></i>
          </div>
        </div>
      </a>
    </div>
    <div class="relative overflow-hidden glass-card border-l-4 border-teal-500 rounded-2xl shadow-xl">
      <a href="dynamic-graph.php?WHAT=windDir&SCALE=day" class="block group focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-teal-400/60 focus-visible:ring-offset-transparent rounded-2xl">
        <span class="absolute -top-16 -right-14 h-36 w-36 bg-teal-400/25 blur-3xl transition-transform duration-500 group-hover:scale-105"></span>
        <div class="relative flex items-center justify-between gap-4">
          <div class="flex flex-col gap-2">
            <span class="text-[0.65rem] font-semibold uppercase tracking-[0.22em] text-teal-500/80">Wind Direction</span>
            <span class="text-3xl font-semibold text-slate-900 dark:text-slate-100 drop-shadow-sm"><span id="windDir">-</span>&#176;</span>
            <span class="text-xs text-slate-600/80 dark:text-slate-300/80">Tap for directional history</span>
          </div>
          <div class="text-teal-400/70 transition-colors duration-300 group-hover:text-teal-300">
            <i class="fas fa-compass fa-2x"></i>
          </div>
        </div>
      </a>
    </div>
  </div>

  <div class="grid grid-cols-1 gap-6 items-start xl:grid-cols-3">
    <div class="glass-card rounded-3xl shadow-2xl xl:col-span-2">
      <div class="flex flex-col gap-3 px-6 py-5 sm:flex-row sm:items-center sm:justify-between border-b border-slate-200/60 dark:border-slate-700/60">
        <h5 class="text-lg font-semibold tracking-tight text-blue-500">Last 24 hours</h5>
        <a href="overview-graph.php?FULL=1#graph" class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-blue-600 via-indigo-600 to-cyan-500 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-900/20 transition-transform duration-300 hover:-translate-y-0.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-blue-400/70">
          Full Screen
        </a>
      </div>
      <div class="px-6 py-5">
        <?php include('overview-graph.php'); ?>
      </div>
    </div>
    <div class="glass-card rounded-3xl shadow-2xl h-full">
      <div class="flex flex-col gap-3 p-6">
        <h5 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Current Garden View</h5>
        <p class="text-sm text-slate-600 dark:text-slate-300">A snapshot direct from the garden station.</p>
        <div class="relative overflow-hidden rounded-2xl ring-1 ring-slate-200/50 dark:ring-slate-700/60 shadow-xl">
          <img src="https://www.smeird.com/images/snap.jpeg" class="w-full h-auto object-cover" alt="Garden view snapshot">
          <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-900/40 via-slate-900/5 to-transparent"></div>
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
      if (!el) {
        return;
      }
      var state = 'disconnected';
      var label = 'Disconnected';
      if (status === true || status === 'connected') {
        state = 'connected';
        label = 'Connected';
      } else if (status === 'reconnecting') {
        state = 'reconnecting';
        label = 'Reconnecting…';
      }
      el.className = 'status-pill mt-4 status-pill--' + state;
      el.innerHTML = '<span class="status-dot" aria-hidden="true"></span><span class="status-text">' + label + '</span>';
    }
  </script>
<?php include('footer.php'); ?>
