// set up basic variables for app

$('#test').html('Code: ' + $('#exhibit-info').data('code'));

var record = document.querySelector('.record');
var stop = document.querySelector('.stop');
var soundClips = document.querySelector('.sound-clips');
var canvas = document.querySelector('.visualizer');
var mainSection = document.querySelector('.main-controls');

// disable stop button while not recording

stop.disabled = true;

// visualiser setup - create web audio api context and canvas

var audioCtx = new (window.AudioContext || webkitAudioContext)();
var canvasCtx = canvas.getContext("2d");


function uploadToPHPServer(blob) {
    // 'msr-' + (new Date).toISOString().replace(/:|\./g, '-') + '.webm'
    let filename = $('#exhibit-info').data('code') + '.ogg';
    var file = new File([blob], filename, {
        type: 'audio/ogg', // 'video/webm'
    });

    // create FormData
    var formData = new FormData();
    formData.append('video-filename', file.name);
    formData.append('video-blob', file);


    console.log(formData);
    let url = $('#exhibit-info').data('postUrl');
    console.log('posting to ' + url);

    /*
    let params = {
        'video-filename' : file.name,
        'video-blob': file
    };

    $.post(url, params, function (data) {
        console.log(data);
        // update the URL on the page? Redirect?
    })
        .fail(function(error) {
            console.error(error)
        })
        .always(function (data) {
            console.warn(data);
        });

     */

    makeXMLHttpRequest(url, formData, function(data) {
        console.log(data);
        console.log('File uploaded to this path:', data.url);

    });
}

function makeXMLHttpRequest(url, data, callback) {
    var request = new XMLHttpRequest();
    request.onreadystatechange = function() {
        if (request.readyState == 4 && request.status == 200) {
            json = JSON.parse(request.response);
            console.log(json);
            callback(json);
        }
    };
    request.open('POST', url);
    request.send(data);
}

//main block for doing the audio recording


if (navigator.mediaDevices.getUserMedia) {
    console.log('getUserMedia supported.');

    var constraints = { audio: true };
    var chunks = [];

    var onSuccess = function(stream) {
        var mediaRecorder = new MediaRecorder(stream);

        visualize(stream);

        record.onclick = function() {
            mediaRecorder.start();
            console.log(mediaRecorder.state);
            console.log("recorder started");
            record.style.background = "red";

            stop.disabled = false;
            record.disabled = true;
        };

        stop.onclick = function() {
            mediaRecorder.stop();
            console.log(mediaRecorder.state);
            console.log("recorder stopped");
            record.style.background = "";
            record.style.color = "";
            // mediaRecorder.requestData();

            stop.disabled = true;
            record.disabled = false;
        };

        mediaRecorder.onstop = function(e) {


            console.log("data available after MediaRecorder.stop() called.");

            var clipName = $('#exhibit-info').data('code'); // prompt('Enter a name for your sound clip?',$('#exhibit-info').data('code'));
            console.log(clipName);
            var clipContainer = document.createElement('article');
            var clipLabel = document.createElement('p');
            var audio = document.createElement('audio');
            var uploadButton = document.createElement('button');

            var deleteButton = document.createElement('button');

            clipContainer.classList.add('clip');
            audio.setAttribute('controls', '');
            deleteButton.textContent = 'Delete';
            deleteButton.className = 'delete';

            uploadButton.textContent = 'Upload';
            uploadButton.className = 'upload';

            if(clipName === null) {
                clipLabel.textContent = 'My unnamed clip';
            } else {
                clipLabel.textContent = clipName;
            }

            clipContainer.appendChild(audio);
            clipContainer.appendChild(clipLabel);
            clipContainer.appendChild(deleteButton);
            clipContainer.appendChild(uploadButton);
            soundClips.appendChild(clipContainer);

            audio.controls = true;
            var blob = new Blob(chunks, { 'type' : 'audio/ogg; codecs=opus' });
            chunks = [];
            var audioURL = window.URL.createObjectURL(blob);
            audio.src = audioURL;
            console.log("recorder stopped");

            deleteButton.onclick = function(e) {
                evtTgt = e.target;
                evtTgt.parentNode.parentNode.removeChild(evtTgt.parentNode);
            };

            uploadButton.onclick = function(e) {
                uploadToPHPServer(blob);
                // evtTgt = e.target;
                // evtTgt.parentNode.parentNode.removeChild(evtTgt.parentNode);
            };

            clipLabel.onclick = function() {
                var existingName = clipLabel.textContent;
                var newClipName = prompt('Enter a new name for your sound clip?');
                if(newClipName === null) {
                    clipLabel.textContent = existingName;
                } else {
                    clipLabel.textContent = newClipName;
                }
            }
        }

        mediaRecorder.ondataavailable = function(e) {
            chunks.push(e.data);
        }
    }

    var onError = function(err) {
        console.log('The following error occured: ' + err);
    };

    $('#enable-microphone').click(function() {
        navigator.mediaDevices.getUserMedia(constraints).then(onSuccess, onError);
    });


} else {
    console.log('getUserMedia not supported on your browser!');
}



function visualize(stream) {
    var source = audioCtx.createMediaStreamSource(stream);

    var analyser = audioCtx.createAnalyser();
    analyser.fftSize = 2048;
    var bufferLength = analyser.frequencyBinCount;
    var dataArray = new Uint8Array(bufferLength);

    source.connect(analyser);
    //analyser.connect(audioCtx.destination);

    draw()

    function draw() {
        WIDTH = canvas.width
        HEIGHT = canvas.height;

        requestAnimationFrame(draw);

        analyser.getByteTimeDomainData(dataArray);

        canvasCtx.fillStyle = 'rgb(200, 200, 200)';
        canvasCtx.fillRect(0, 0, WIDTH, HEIGHT);

        canvasCtx.lineWidth = 2;
        canvasCtx.strokeStyle = 'rgb(0, 0, 0)';

        canvasCtx.beginPath();

        var sliceWidth = WIDTH * 1.0 / bufferLength;
        var x = 0;


        for(var i = 0; i < bufferLength; i++) {

            var v = dataArray[i] / 128.0;
            var y = v * HEIGHT/2;

            if(i === 0) {
                canvasCtx.moveTo(x, y);
            } else {
                canvasCtx.lineTo(x, y);
            }

            x += sliceWidth;
        }

        canvasCtx.lineTo(canvas.width, canvas.height/2);
        canvasCtx.stroke();

    }
}

window.onresize = function() {
    canvas.width = mainSection.offsetWidth;
}

window.onresize();