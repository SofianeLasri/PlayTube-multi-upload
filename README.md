# What is it?
This is a small script allowing the automated sending of videos placed in a folder on the web server (or simply the server). It contains some bugs and I would like to add some features like the use of site cookies (to avoid security problems), but also sending video from the browser.
There is a small bug -> I incorrectly created the date time variable to register in the database, so the videos sent will have the date of sending, 1970.

Currently the script only works with ffmpeg, it's stupid but I haven't done the part without ffmpeg yet... x)

# How it work?
By dint of fixing Playtube bugs ($ 80 for that ... what a shame), I learned how it works. So, instead of adding the functionality directly to the site (because I don't have the time to learn how it communicates with the database, hence having to configure it manually), I created a small external script that uses the same commands as PlayTube to transcode and send the videos.

Check index.php for installation, indicates the Playtube's database connection informations.