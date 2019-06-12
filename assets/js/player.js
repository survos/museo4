Amplitude = require('amplitudejs');
Foundation = require('foundation');

console.log('in player');

// Amplitude.init({"songs": []});

let songs = [];
$.get("/exhibits-feed.json", function(data) {
    console.log(data);
    data.forEach(function (d) {
        let song = {
            artist: "Description of exhibit",
            album: "Sala #1",
            name: d.filename,
            cover_art_url: "https://dummyimage.com/300x200/000/fff&text=" + d.code,
            url: d.s3Url
        };
        console.log(song);
        songs.push(song);

        // Amplitude.addSong(song);

    });
    console.log(Amplitude.songs);
    Amplitude.init({
        "songs": songs
    });
});

if (0)
Amplitude.init({
    "songs": [
        {
            "name": "Song Name 1",
            "artist": "Artist Name",
            "album": "Album Name",
            "url": "/song/url.mp3",
            "cover_art_url": "/cover/art/url.jpg"
        },
        {
            "name": "Song Name 2",
            "artist": "Artist Name",
            "album": "Album Name",
            "url": "/song/url.mp3",
            "cover_art_url": "/cover/art/url.jpg"
        },
        {
            "name": "Song Name 3",
            "artist": "Artist Name",
            "album": "Album Name",
            "url": "/song/url.mp3",
            "cover_art_url": "/cover/art/url.jpg"
        }
    ]
});


// Amplitude.addPlaylist('x', {}, {});


