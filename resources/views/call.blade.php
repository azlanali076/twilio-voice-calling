<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Call</title>
</head>
<body>
<input type="tel" name="number" id="number" class="form-control">
<button type="button" onclick="makeOutgoingCall()" >Call</button>
<button style="display: none;" id="disconnect" typeof="button" onclick="hangUpCall()" >Disconnect</button>
<div id="incoming_btns" style="display: none;">
    <button type="button" id="accept_call" >Accept</button>
    <button type="button" id="reject_call" >Reject</button>
</div>

<script src="{{ asset('twilio.min.js') }}"></script>
<script>
    var device,token,call;
    function getToken(){
        fetch("/api/token").then(res => res.json()).then(resJson => {
            token = resJson.token;
            getDevice();
        });
    }
    function getDevice(){
        device = new Twilio.Device(token, {
            logLevel: 1,
            // Set Opus as our preferred codec. Opus generally performs better, requiring less bandwidth and
            // providing better audio quality in restrained network conditions.
            codecPreferences: ["opus", "pcmu"]
        });
        device.on("registered", function () {
            console.log("Twilio.Device Ready to make and receive calls!");
        });

        device.on("error", function (error) {
            console.log("Twilio.Device Error: " + error.message);
        });

        device.on("incoming", handleIncomingCall);

        device.register();
    }

    function hangUpCall(){
        if(call){
            call.disconnect();
            document.getElementById('disconnect').style.display = 'none';
        }
    }

    function makeOutgoingCall() {
        var params = {
            // get the phone number to call from the DOM
            To: document.getElementById('number').value,
            agent: '{{ env('TWILIO_IDENTITY') }}'
        };

        if (device) {
            // Twilio.Device.connect() returns a Call object
            device.connect({ params }).then(c => {
                call = c;
                document.getElementById('disconnect').style.display = 'block';
            });

        } else {
            console.log("Unable to make call.");
        }
    }

    function handleIncomingCall(call){
        document.getElementById('incoming_btns').style.display = 'block';
        document.getElementById('accept_call').onclick = function() {
            acceptCall(call);
        }
        document.getElementById('reject_call').onclick = function() {
            rejectCall(call);
        }
    }

    function acceptCall(c){
        c.accept();
        call = c;
        document.getElementById('incoming_btns').style.display = 'none';
        document.getElementById('disconnect').style.display = 'block';
    }

    function rejectCall(call){
        call.reject();
        document.getElementById('incoming_btns').style.display = 'none';
        document.getElementById('disconnect').style.display = 'none';
    }

    getToken();
</script>
</body>
</html>
