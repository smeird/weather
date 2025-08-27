<?php
include('header.php');
include('dbconn.php');
?>

</head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.js" type="text/javascript"></script>




<style>
  a:hover {
    /* REMOVE drop Shadow when hovering only */
    text-decoration: none;
    -moz-box-shadow: none;
    -webkit-box-shadow: none;
    box-shadow: none;
  }
</style>

<div class="container-fluid">
  <!-- Page Heading -->
  <div class="d-sm-flex align-items-center justify-content-between mb-2">
    <h1 class="h3 mb-0 text-gray-800">Current Conditions</h1>
  </div>




  <div class="row">

    <div class="col-xl-3 col-md-6 mb-2">
      <div class="card border-left-danger shadow ">
        <a href=https://www.smeird.com/newgraph.php?WHAT=outTemp&SCALE=day>
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Outside Temprature</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><span id=OutTemp>-</span> &#176;C</div>
              </div>
              <div class="col-auto">
                <i class="fas fa-temperature-low fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </a>
      </div>
    </div>


    <div class="col-xl-3 col-md-6 mb-2">
      <div class="card border-left-success shadow ">
        <a href=http://www.smeird.com/newgraph.php?WHAT=outHumidity&SCALE=day>
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Outside Humidty</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><span id=OutHumidty>-</span> %</div>
              </div>
              <div class="col-auto">
                <i class="fas fa-bolt fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </a>
      </div>
    </div>


    <div class="col-xl-3 col-md-6 mb-2">
      <div class="card border-left-info shadow ">
        <a href=http://www.smeird.com/newgraph.php?WHAT=windSpeed&SCALE=day>
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Wind Speed</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><span id=windSpeed_kph>-</span> kph</div>
              </div>
              <div class="col-auto">
                <i class="fas fa-wind fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </a>
      </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-2">
      <div class="card border-left-warning shadow ">
        <a href=http://www.smeird.com/newgraph.php?WHAT=Barometer&SCALE=day>
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Barometer</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><span id=Barometer>-</span> mbar</div>
              </div>
              <div class="col-auto">
                <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </a>
      </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-2">
      <div class="card border-left-primary shadow ">
        <a href=http://www.smeird.com/newgraph.php?WHAT=Rain&SCALE=day>
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Rain Today</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><span id=drain>-</span> cm</div>
              </div>
              <div class="col-auto">
                <i class="fas fa-tint fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </a>
      </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-2">
      <div class="card border-left-primary shadow">
        <a href=https://www.smeird.com/newgraph.php?WHAT=rain&TYPE=MINMAX&SCALE=month>
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Rain this Month</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><span id=mrain>-</span> cm</div>
              </div>
              <div class="col-auto">
                <i class="fas fa-tint fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </a>
      </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-2">
      <div class="card border-left-info shadow">
        <a href=https://www.smeird.com/newgraph.php?WHAT=windGust&SCALE=day>
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Wind Gust</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                  <span id=windGust_kph>-</span> kph :
                  <span id=windGustDir>-</span> Deg
                </div>
              </div>
              <div class="col-auto">
                <i class="fas fa-wind fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </a>
      </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-2">
      <div class="card border-left-info shadow ">
        <a href=http://www.smeird.com/newgraph.php?WHAT=windDir&SCALE=day>
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Wind Direction</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><span id=windDir>-</span> Deg</div>
              </div>
              <div class="col-auto">
                <i class="fas fa-wind fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </a>
      </div>
    </div>


    <div class="col-lg-12">

      <!-- Dropdown Card Example -->
      <div class="card shadow mb-2">
        <!-- Card Header - Dropdown -->
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
          <h5 class="m-0 font-weight-bold text-primary">Last 24 hours</h5>
          <div class="dropdown no-arrow">
            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
              <div class="dropdown-header">Veiws:</div>
              <a class="dropdown-item" href=graph3.php?FULL=1#graph>Full Screen</a>

            </div>
          </div>
        </div>
        <!-- Card Body -->
        <div class="card-body">
          <?php include('graph3.php'); ?></div>
      </div>


    </div>
  </div>

  <div class="container-fluid">
    <div class="card shadow">
      <div class="card-body">


        <h5 class="card-title">Current Garden View</h5>
        <p class="card-text">Snap Shot of conditions</p>
        <img src="https://www.smeird.com/snap.jpeg" class="img-fluid img-thumbnail" alt="Card image">
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
        document.getElementById("OutHumidty").innerHTML = dp(obj.outHumidity);
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
