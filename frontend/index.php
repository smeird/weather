<?php
// Load shared page components and database connection
include('header.php');
require_once '../dbconn.php';
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.js" type="text/javascript"></script>

<div>
  <div class="flex flex-col sm:flex-row items-center justify-between mb-2">
    <h1 class="text-2xl text-gray-800">Current Conditions</h1>
  </div>
  <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="bg-white border-l-4 border-red-500 shadow rounded p-4">
      <a href="dynamic-graph.php?WHAT=outTemp&SCALE=day" class="block hover:no-underline">
        <div class="flex items-center">
          <div class="flex-grow mr-2">
            <div class="text-[0.525rem] font-bold text-red-500 uppercase mb-1">Outside Temperature</div>
            <div class="text-xl font-bold text-gray-800"><span id=OutTemp>-</span> &#176;C</div>
          </div>
          <div class="flex-shrink-0">
            <i class="fas fa-temperature-low fa-2x text-gray-300"></i>
          </div>
        </div>
      </a>
    </div>
    <div class="bg-white border-l-4 border-green-500 shadow rounded p-4">
      <a href="dynamic-graph.php?WHAT=outHumidity&SCALE=day" class="block hover:no-underline">
        <div class="flex items-center">
          <div class="flex-grow mr-2">
            <div class="text-[0.525rem] font-bold text-green-500 uppercase mb-1">Outside Humidity</div>
            <div class="text-xl font-bold text-gray-800"><span id=OutHumidity>-</span> %</div>
          </div>
          <div class="flex-shrink-0">
            <i class="fas fa-bolt fa-2x text-gray-300"></i>
          </div>
        </div>
      </a>
    </div>
    <div class="bg-white border-l-4 border-cyan-500 shadow rounded p-4">
      <a href="dynamic-graph.php?WHAT=windSpeed&SCALE=day" class="block hover:no-underline">
        <div class="flex items-center">
          <div class="flex-grow mr-2">
            <div class="text-[0.525rem] font-bold text-cyan-500 uppercase mb-1">Wind Speed</div>
            <div class="text-xl font-bold text-gray-800"><span id=windSpeed_kph>-</span> kph</div>
          </div>
          <div class="flex-shrink-0">
            <i class="fas fa-wind fa-2x text-gray-300"></i>
          </div>
        </div>
      </a>
    </div>
    <div class="bg-white border-l-4 border-yellow-500 shadow rounded p-4">
      <a href="dynamic-graph.php?WHAT=barometer&SCALE=day" class="block hover:no-underline">
        <div class="flex items-center">
          <div class="flex-grow mr-2">
            <div class="text-[0.525rem] font-bold text-yellow-500 uppercase mb-1">Barometer</div>
            <div class="text-xl font-bold text-gray-800"><span id=Barometer>-</span> mbar</div>
          </div>
          <div class="flex-shrink-0">
            <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
          </div>
        </div>
      </a>
    </div>
    <div class="bg-white border-l-4 border-purple-500 shadow rounded p-4">
      <a href="dynamic-graph.php?WHAT=dewpoint&SCALE=day" class="block hover:no-underline">
        <div class="flex items-center">
          <div class="flex-grow mr-2">
            <div class="text-[0.525rem] font-bold text-purple-500 uppercase mb-1">Dew Point</div>
            <div class="text-xl font-bold text-gray-800"><span id=Dewpoint>-</span> &#176;C</div>
          </div>
          <div class="flex-shrink-0">
            <i class="fas fa-thermometer-half fa-2x text-gray-300"></i>
          </div>
        </div>
      </a>
    </div>
    <div class="bg-white border-l-4 border-indigo-500 shadow rounded p-4">
      <a href="dynamic-graph.php?WHAT=windchill&SCALE=day" class="block hover:no-underline">
        <div class="flex items-center">
          <div class="flex-grow mr-2">
            <div class="text-[0.525rem] font-bold text-indigo-500 uppercase mb-1">Wind Chill</div>
            <div class="text-xl font-bold text-gray-800"><span id=Windchill>-</span> &#176;C</div>
          </div>
          <div class="flex-shrink-0">
            <i class="fas fa-snowflake fa-2x text-gray-300"></i>
          </div>
        </div>
      </a>
    </div>
    <div class="bg-white border-l-4 border-blue-500 shadow rounded p-4">
      <a href="dynamic-graph.php?WHAT=rain&SCALE=day" class="block hover:no-underline">
        <div class="flex items-center">
          <div class="flex-grow mr-2">
            <div class="text-[0.525rem] font-bold text-blue-500 uppercase mb-1">Rain Today</div>
            <div class="text-xl font-bold text-gray-800"><span id=drain>-</span> cm</div>
          </div>
          <div class="flex-shrink-0">
            <i class="fas fa-tint fa-2x text-gray-300"></i>
          </div>
        </div>
      </a>
    </div>
    <div class="bg-white border-l-4 border-blue-500 shadow rounded p-4">
      <a href="dynamic-graph.php?WHAT=rain&TYPE=MINMAX&SCALE=month" class="block hover:no-underline">
        <div class="flex items-center">
          <div class="flex-grow mr-2">
            <div class="text-[0.525rem] font-bold text-blue-500 uppercase mb-1">Rain this Month</div>
            <div class="text-xl font-bold text-gray-800"><span id=mrain>-</span> cm</div>
          </div>
          <div class="flex-shrink-0">
            <i class="fas fa-tint fa-2x text-gray-300"></i>
          </div>
        </div>
      </a>
    </div>
    <div class="bg-white border-l-4 border-cyan-500 shadow rounded p-4">
      <a href="dynamic-graph.php?WHAT=windGust&SCALE=day" class="block hover:no-underline">
        <div class="flex items-center">
          <div class="flex-grow mr-2">
            <div class="text-[0.525rem] font-bold text-cyan-500 uppercase mb-1">Wind Gust</div>
            <div class="text-xl font-bold text-gray-800">
              <span id=windGust_kph>-</span> kph :
              <span id=windGustDir>-</span> Deg
            </div>
          </div>
          <div class="flex-shrink-0">
            <i class="fas fa-wind fa-2x text-gray-300"></i>
          </div>
        </div>
      </a>
    </div>
    <div class="bg-white border-l-4 border-cyan-500 shadow rounded p-4">
      <a href="dynamic-graph.php?WHAT=windDir&SCALE=day" class="block hover:no-underline">
        <div class="flex items-center">
          <div class="flex-grow mr-2">
            <div class="text-[0.525rem] font-bold text-cyan-500 uppercase mb-1">Wind Direction</div>
            <div class="text-xl font-bold text-gray-800"><span id=windDir>-</span> Deg</div>
          </div>
          <div class="flex-shrink-0">
            <i class="fas fa-wind fa-2x text-gray-300"></i>
          </div>
        </div>
      </a>
    </div>
  </div>

  <div class="mt-4">
    <div class="bg-white shadow rounded mb-2">
      <div class="px-4 py-3 flex items-center justify-between border-b">
        <h5 class="font-bold text-blue-500">Last 24 hours</h5>
        <a href="overview-graph.php?FULL=1#graph" class="inline-block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Full Screen</a>
      </div>
      <div class="p-4">
        <?php include('overview-graph.php'); ?>
      </div>
    </div>
  </div>
</div>

<div>
  <div class="flex justify-center">
    <div class="w-full md:w-1/2 xl:w-1/3">
      <div class="bg-white shadow rounded p-4">
        <h5 class="text-lg font-semibold">Current Garden View</h5>
        <p class="mb-4">Snap Shot of conditions</p>
        <img src="https://www.smeird.com/images/snap.jpeg" class="w-full h-auto rounded" alt="Card image">
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
      if (status === true || status === 'connected') {
        el.className = "flex items-center px-4 mt-2 text-green-500";
        el.innerHTML = '<i class="fas fa-circle mr-2"></i>Connected';
      } else if (status === 'reconnecting') {
        el.className = "flex items-center px-4 mt-2 text-yellow-500";
        el.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Reconnecting';
      } else {
        el.className = "flex items-center px-4 mt-2 text-red-500";
        el.innerHTML = '<i class="fas fa-circle mr-2"></i>Disconnected';
      }
    }
  </script>
<?php include('footer.php'); ?>
