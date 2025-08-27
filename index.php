<?php
// Load shared page components and database connection
include('header.php');
include('dbconn.php');
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.js" type="text/javascript"></script>

<div class="max-w-screen-xl mx-auto p-4">
  <div class="flex flex-col sm:flex-row items-center justify-between mb-2">
    <h1 class="text-2xl text-gray-800">Current Conditions</h1>
  </div>
  <div class="flex flex-wrap -mx-2">
    <div class="w-full md:w-1/2 xl:w-1/4 p-2">
      <div class="bg-white border-l-4 border-red-500 shadow rounded p-4">
        <a href="https://www.smeird.com/newgraph.php?WHAT=outTemp&SCALE=day" class="block hover:no-underline">
          <div class="flex items-center">
            <div class="flex-grow mr-2">
              <div class="text-xs font-bold text-red-500 uppercase mb-1">Outside Temperature</div>
              <div class="text-xl font-bold text-gray-800"><span id=OutTemp>-</span> &#176;C</div>
            </div>
            <div class="flex-shrink-0">
              <i class="fas fa-temperature-low fa-2x text-gray-300"></i>
            </div>
          </div>
        </a>
      </div>
    </div>
    <div class="w-full md:w-1/2 xl:w-1/4 p-2">
      <div class="bg-white border-l-4 border-green-500 shadow rounded p-4">
        <a href="http://www.smeird.com/newgraph.php?WHAT=outHumidity&SCALE=day" class="block hover:no-underline">
          <div class="flex items-center">
            <div class="flex-grow mr-2">
              <div class="text-xs font-bold text-green-500 uppercase mb-1">Outside Humidity</div>
              <div class="text-xl font-bold text-gray-800"><span id=OutHumidity>-</span> %</div>
            </div>
            <div class="flex-shrink-0">
              <i class="fas fa-bolt fa-2x text-gray-300"></i>
            </div>
          </div>
        </a>
      </div>
    </div>
    <div class="w-full md:w-1/2 xl:w-1/4 p-2">
      <div class="bg-white border-l-4 border-cyan-500 shadow rounded p-4">
        <a href="http://www.smeird.com/newgraph.php?WHAT=windSpeed&SCALE=day" class="block hover:no-underline">
          <div class="flex items-center">
            <div class="flex-grow mr-2">
              <div class="text-xs font-bold text-cyan-500 uppercase mb-1">Wind Speed</div>
              <div class="text-xl font-bold text-gray-800"><span id=windSpeed_kph>-</span> kph</div>
            </div>
            <div class="flex-shrink-0">
              <i class="fas fa-wind fa-2x text-gray-300"></i>
            </div>
          </div>
        </a>
      </div>
    </div>
    <div class="w-full md:w-1/2 xl:w-1/4 p-2">
      <div class="bg-white border-l-4 border-yellow-500 shadow rounded p-4">
        <a href="http://www.smeird.com/newgraph.php?WHAT=Barometer&SCALE=day" class="block hover:no-underline">
          <div class="flex items-center">
            <div class="flex-grow mr-2">
              <div class="text-xs font-bold text-yellow-500 uppercase mb-1">Barometer</div>
              <div class="text-xl font-bold text-gray-800"><span id=Barometer>-</span> mbar</div>
            </div>
            <div class="flex-shrink-0">
              <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
            </div>
          </div>
        </a>
      </div>
    </div>
    <div class="w-full md:w-1/2 xl:w-1/4 p-2">
      <div class="bg-white border-l-4 border-blue-500 shadow rounded p-4">
        <a href="http://www.smeird.com/newgraph.php?WHAT=Rain&SCALE=day" class="block hover:no-underline">
          <div class="flex items-center">
            <div class="flex-grow mr-2">
              <div class="text-xs font-bold text-blue-500 uppercase mb-1">Rain Today</div>
              <div class="text-xl font-bold text-gray-800"><span id=drain>-</span> cm</div>
            </div>
            <div class="flex-shrink-0">
              <i class="fas fa-tint fa-2x text-gray-300"></i>
            </div>
          </div>
        </a>
      </div>
    </div>
    <div class="w-full md:w-1/2 xl:w-1/4 p-2">
      <div class="bg-white border-l-4 border-blue-500 shadow rounded p-4">
        <a href="https://www.smeird.com/newgraph.php?WHAT=rain&TYPE=MINMAX&SCALE=month" class="block hover:no-underline">
          <div class="flex items-center">
            <div class="flex-grow mr-2">
              <div class="text-xs font-bold text-blue-500 uppercase mb-1">Rain this Month</div>
              <div class="text-xl font-bold text-gray-800"><span id=mrain>-</span> cm</div>
            </div>
            <div class="flex-shrink-0">
              <i class="fas fa-tint fa-2x text-gray-300"></i>
            </div>
          </div>
        </a>
      </div>
    </div>
    <div class="w-full md:w-1/2 xl:w-1/4 p-2">
      <div class="bg-white border-l-4 border-cyan-500 shadow rounded p-4">
        <a href="https://www.smeird.com/newgraph.php?WHAT=windGust&SCALE=day" class="block hover:no-underline">
          <div class="flex items-center">
            <div class="flex-grow mr-2">
              <div class="text-xs font-bold text-cyan-500 uppercase mb-1">Wind Gust</div>
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
    </div>
    <div class="w-full md:w-1/2 xl:w-1/4 p-2">
      <div class="bg-white border-l-4 border-cyan-500 shadow rounded p-4">
        <a href="http://www.smeird.com/newgraph.php?WHAT=windDir&SCALE=day" class="block hover:no-underline">
          <div class="flex items-center">
            <div class="flex-grow mr-2">
              <div class="text-xs font-bold text-cyan-500 uppercase mb-1">Wind Direction</div>
              <div class="text-xl font-bold text-gray-800"><span id=windDir>-</span> Deg</div>
            </div>
            <div class="flex-shrink-0">
              <i class="fas fa-wind fa-2x text-gray-300"></i>
            </div>
          </div>
        </a>
      </div>
    </div>
  </div>

  <div class="mt-4">
    <div class="bg-white shadow rounded mb-2">
      <div class="px-4 py-3 flex items-center justify-between border-b">
        <h5 class="font-bold text-blue-500">Last 24 hours</h5>
        <a class="text-sm text-blue-500 hover:underline" href="graph3.php?FULL=1#graph">Full Screen</a>
      </div>
      <div class="p-4">
        <?php include('graph3.php'); ?>
      </div>
    </div>
  </div>
</div>

<div class="max-w-screen-xl mx-auto p-4">
  <div class="flex justify-center">
    <div class="w-full md:w-1/2 xl:w-1/3">
      <div class="bg-white shadow rounded p-4">
        <h5 class="text-lg font-semibold">Current Garden View</h5>
        <p class="mb-4">Snap Shot of conditions</p>
        <img src="https://www.smeird.com/snap.jpeg" class="w-full h-auto rounded" alt="Card image">
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
    var connected_flag = 1
    var mqtt;
    var reconnectTimeout = 2000;
    var host = "mqtt.smeird.com";
    var port = 8083;
    var clean = 0;
    var obj = 0.001;

    // Create a client instance
    client = new Paho.MQTT.Client(host, port, uuidv4());

    // set callback handlers
    client.onConnectionLost = onConnectionLost;
    client.onMessageArrived = onMessageArrived;

    // connect the client
    client.connect({
        useSSL: true,
        onSuccess: onConnect
    });

    function uuidv4() {
      return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = Math.random() * 16 | 0,
          v = c == 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
      });
    }

    // called when the client connects
    function onConnect() {
      // Once a connection has been made, make a subscription and send a message.
      console.log("onConnect");
      client.subscribe("weather/loop");
      document.getElementById("connect").innerHTML = "Live";
    }

    // called when the client loses its connection
    function onConnectionLost(responseObject) {
      if (responseObject.errorCode !== 0) {
        console.log("onConnectionLost:" + responseObject.errorMessage);
        document.getElementById("connect").innerHTML = responseObject.errorMessage;
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
        document.getElementById("drain").innerHTML = dp(obj.dayRain_cm);
        document.getElementById("mrain").innerHTML = dp(obj.monthRain_cm);
      }
    }
    //ll
    function dp(x) {
      return Number.parseFloat(x).toFixed(2);
    }
  </script>
</div>
</div>
</div>
</body>
</html>
