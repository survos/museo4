Amplitude = require('amplitudejs');
Foundation = require('foundation');

console.log('in player');


/*
Amplitude.addSong({
    "name": "Song Name 1",
    "artist": "Artist Name",
    "album": "Album Name",
    "url": "/song/url.mp3",
    "cover_art_url": "/cover/art/url.jpg"
});
console.log(Amplitude.getSongs());
*/

let songs = [];
$.get("/exhibits-feed.json", function(data) {
    // Amplitude.init({debug: true, "songs": []});
    data.forEach(function (d) {
        let song = {
            artist: d.title,
            album: d.description,
            name: d.filename,
            cover_art_url: "https://dummyimage.com/300x200/000/fff&text=" + d.code,
            url: d.s3Url
        };
        songs.push(song);
        console.log(song);

        if (false) {
            Amplitude.addSong(song);
            console.log(Amplitude.getSongs());
        }

    });

    Amplitude.init({
        // debug: true,
        "songs": songs
    });
    /*
    console.log(songs, Amplitude.getSongs());
    if (0)
     */
});


if (0)
Amplitude.init({
    debug: true,
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


