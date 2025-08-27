<?php
 include ('header.php');
// include ('dbconn.php');
 ?>
</head>
    <title>Home Links</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script src="http://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.js" type="text/javascript"></script>


  <div  class="container-fluid">
    <!-- Page Heading -->


<div class="row">

      <div class="col-lg-2 col-md-2 mb-2">
                    <div id=clouds_S class="card border-left-success shadow ">
                      <a href=http://ob.smeird.com/newgraph.php?WHAT=clouds&SCALE=day>
                      <div class="card-body">
                        <div class="row no-gutters align-items-center">
                          <div class="col mr-2">
                            <div id=clouds_T class="text-xs font-weight-bold text-success text-uppercase mb-1">Cloud Temprature</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><span id=clouds>-</span>&degC</div>
                          </div>
                          <div class="col-auto">
                            <i id='clouds_I' class="fas  fa-2x text-gray-300 fa"></i>
                          </div>
                        </div>
                      </div></a>
                    </div>
                </div>
