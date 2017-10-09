# Movie Explorer -

This is a movie website where you are able to browse/search for movies that you would like to watch and add them to your personal watchlist. Your watchlist will get persisted to a database in order for you to make changes over time and view it at a later date.
 
# Prerequisites-

- PHP
- NodeJS
- XAMPP
- Materializecss
- A TMDB API key
- A dataset containing movie titles and tmdbids

# Installing -

You will need NodeJS in order to install Materializecss.

After installing NodeJS you can navigate to your projects folder and install Materializecss by typing the following command into your commandline.

npm install materialize-css

In Xampp you will need to create a movie_user with password 'password'

# Why I chose to do this project -

I started this for a project in school where the requirements were that I had to use PHP and MYSQL. Initially I just wanted to have a dataset stored in a mysql database and have all data come from that source. I then ran into a challenge of not having any images or overviews of the movies in my database. That sounded like a pretty boring website choosing movies to add to a watchlist without any details other than a title so I decided to use an external API to provide me with a JSON object that would contain lots of useful information that I could include in my website. The website is also making use of AJAX in the searchbox to have the typed string be used in a MYSQL query to check which titles contain that substring.
