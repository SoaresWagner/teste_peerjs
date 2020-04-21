<?php

	$id_client = "";
	
	if(isset($_GET['id_client'])){
		$id_client = $_GET['id_client'];
	}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Telemedicina Admed Sistemas</title>
	<style>
		body {    
			margin: 0 !important;
			padding: 0 !important;
		}
		.widget_body {
			width: 100%;
			height: 100%;
			position: relative;
		}
		.large_video {
			height: 100%;
			width: 100%;
			background: gray;
		
			-- object-fit: cover;
		}
		.mini_video {
			position: absolute;
			height: 30%;
			width: 30%;
			bottom: 5px;
			right: 0px;
			-- background: black;		
			-- object-fit: cover;
		}
		.decline {
			position: absolute;
			border: none;
			background-color: rgba(255,255,255,0);			
			bottom: 5px;
			right: 50%;			
		}
		
	</style>
	
	<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha256-pasqAKBDmFT4eHoN2ndd6lN370kFiGUFyTiUHWhU7k8=" crossorigin="anonymous"></script>
	<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js" integrity="sha384-6khuMg9gaYr5AxOqhkVIODVIvm9ynTT5J4V1cfthmT+emCG6yVmEZsRHdxlotUnm" crossorigin="anonymous"></script>
	
</head>
<body>
	
	<div class="flex-center position-ref full-height">
            
		<div class="content">
			<div class="row">
				<div class="col-md-12">
					<div class="widget_body">
						<video class="large_video" src="#" autoplay id="pattern" muted></video>
						<video class="mini_video" src="#" autoplay id="local" muted></video>
						
						<button id="decline" name="decline" class="decline">
							<a href="#"><img src="img/decline.png" /></a>
						</button>
										
					</div>
					<br>
					<div>
						<input type="text" name="peer_id" id="peer_id">
						<button type="button" id="login">Connect</button>
						<button type="button" id="call">Call</button>
						<span id="id"></span>
					</div>
				</div>			
			</div>
		</div>
		
	</div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://unpkg.com/peerjs@1.0.0/dist/peerjs.min.js"></script>

    <script>
        (function() {
            var app = {
                peers: []
            }
			
            app.checkRTC = function(cb) {
                navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia || navigator.oGetUserMedia;

                if (!navigator.getUserMedia) {
                    return cb({ err: 'RTC Failed ' }, false)
                }

                navigator.getUserMedia({ video: true, audio: { echoCancellation: true} }, cb, function(err) {
                    console.log('CheckRTC => ', err)
                })
            }

            app.onRecieveStream = function(stream, name) {
                var video = $('#' + name)[0]                
				video.srcObject = stream;
            }

            app.newPeer = function() {
                $("#id").text(app.peer.id)
            }

            app.login = function() {
                var peerID = $('#peer_id').val();

                if (peerID) {
                    app.peers.push(peerID)
                    con = app.peer.connect(peerID)
                }
            }

            app.peerConnect = function(con) {
                app.peer.peer_id = con.peer;				
                app.peer.con = con;				
                console.log('A new connection');
            }

            app.call = function() {
				//var call  = app.peer.call(app.peer.peer_id, window.localStream)
                var call  = app.peer.call($("#peer_id").val(), window.localStream)

                call.on('stream', function(stream) {
                    app.onRecieveStream(stream, 'pattern')
                })
            }

            app.onReceiveCall = function(call) {
                call.answer(window.localStream)
                call.on('stream', function(stream) {
                    app.onRecieveStream(stream, 'pattern')
                })
            }

            app.peerCall = function(call) {
                return app.onReceiveCall(call)
            }	
				
			app.decline = function() {
				console.log("disconnect");
				app.peer.disconnect();
				app.peer.destroy();
			}

            app.init = function() {
                app.peer = new Peer({key:'peerjs', host:'tele-server.admedsistemas.com.br', path:'/myapp', debug: 3})                                

                app.peer.on('open', app.newPeer)
                app.peer.on('connection', app.peerConnect)
                app.peer.on('call', app.peerCall)

                app.checkRTC(function(stream) {
                    window.localStream = stream
                    app.onRecieveStream(stream, 'local')
                })
				
				app.peer.on('error', function(err) {
				   console.log('DEU ERRO AQUI', err);				   
				});

                $("#login").on('click', app.login)
                $("#call").on('click', app.call)
                $("#decline").on('click', app.decline)
								
            }

            app.init()
        })();
    </script>
</body>
</html>
