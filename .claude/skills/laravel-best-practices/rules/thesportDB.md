The current free API key is: 123

free key : 
https://www.thesportsdb.com/api/v1/json/123/searchteams.php?t=Arsenal

Images
Our site has a huge amount of images for things like events, players and teams. Most are fan created.
There are 2 types of images available. JPEG fanart and transparent PNG mostly used for badges and logos.
You can see the different types of artwork and sizes on this page.
You can access any image from the front-end using the image URL from the returned JSON data.


Original 720px- /league/fanart/xpwsrw1421853005.jpg
Medium version of league fanart
Medium 500px - /league/fanart/xpwsrw1421853005.jpg/medium
Medium version of league fanart
Small 250px - /league/fanart/xpwsrw1421853005.jpg/small
Small version of league fanart
Tiny 50px - /league/fanart/xpwsrw1421853005.jpg/tiny


Rate Limit
The site has different rate limits for different levels of users. You will recieve a "429" http header if you breach the limit, then you will need to wait another minute until requests will work again. We enforce these not just to differentiate our tiers, but to keep the overall performance of the site stable.

arrow list Free users 30 requests per minute.


Search Teams
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
Search for any sports team by its name. {strTeam}
https://www.thesportsdb.com/api/v1/json/123/searchteams.php?t=Arsenal
NOTE: Free tier limited to just "Arsenal". Upgrade for full search.

Search Events:
Type:  Parameter: 
Type:  Parameter: 
Type:  Parameter: 
Free Limit:  Premium Limit: 
Example: JSON Data
Example with season: JSON Data
Example with date: JSON Data
Example with filename: JSON Data
Description: Search for any sports event by it's title with extra filters for season, date or filename: {strEvent}
Optional strings: {strSeason} {strDate} {strFilename}
https://www.thesportsdb.com/api/v1/json/123/searchevents.php?e=Arsenal_vs_Chelsea

Search Filename:
Type:  Parameter: 
Type:  Parameter: 
Free Limit:  Premium Limit: 
Example: JSON Data
Example with season: JSON Data
Description: Search for any sports event by filename with an optional season filter. {strFilename}
Optional strings: {strSeason}
https://www.thesportsdb.com/api/v1/json/123/searchfilename.php?e=English_Premier_League_2015-04-26_Arsenal_vs_Chelsea
https://www.thesportsdb.com/api/v1/json/123/searchfilename.php?e=English_Premier_League_2015-04-26_Arsenal_vs_Chelsea&s=2016-2017


Search Players:
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
Description: Search for any sports person by their main or alternate name. {strPlayer}
https://www.thesportsdb.com/api/v1/json/123/searchplayers.php?p=Danny_Welbeck

Search Venues:
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
Description: Search any venue by its name or alternate name {strVenue}
https://www.thesportsdb.com/api/v1/json/123/searchvenues.php?v=Wembley


Lookup League:
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
Lookup a leagues details using its ID {idLeague}
https://www.thesportsdb.com/api/v1/json/123/lookupleague.php?id=4328

Lookup League Table:
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
Lookup a current league points table using its ID: {idLeague} Optional: {strSeason} (*Limited to featured soccer leagues ONLY)
https://www.thesportsdb.com/api/v1/json/123/lookuptable.php?l=4328
https://www.thesportsdb.com/api/v1/json/123/lookuptable.php?l=4328&s=2020-2021

Lookup Team
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
Lookup a teams details using its ID. {idTeam}
https://www.thesportsdb.com/api/v1/json/123/lookupteam.php?id=133604

Lookup Team Equipment
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
Lookup a teams historical and current equipment using its ID. {idTeam}
https://www.thesportsdb.com/api/v1/json/123/lookupequipment.php?id=133597

Lookup Player
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
Lookup a players details using their ID. {idPlayer}
https://www.thesportsdb.com/api/v1/json/123/lookupplayer.php?id=34145937

Lookup Player Honours
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
Lookup all honours for a player using their ID. {idPlayer}
https://www.thesportsdb.com/api/v1/json/123/lookuphonours.php?id=34147178

Lookup Player Former Teams
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
Lookup all former teams for a player using their ID. {idPlayer}
https://www.thesportsdb.com/api/v1/json/123/lookupformerteams.php?id=34147178

Lookup Player Milestones
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
Lookup all Milestones for a player using their ID. {idPlayer}
https://www.thesportsdb.com/api/v1/json/123/lookupmilestones.php?id=34161397

Lookup Player Contracts
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
Lookup all contracts for a player using their ID. {idPlayer}
https://www.thesportsdb.com/api/v1/json/123/lookupcontracts.php?id=34147178

Lookup Player Results
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
Lookup all results for a player using their ID. {idPlayer}
https://www.thesportsdb.com/api/v1/json/123/playerresults.php?id=34160573

Lookup Player Statistics
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
Lookup the statistics for a player using its ID. {idPlayer}
https://www.thesportsdb.com/api/v1/json/123/lookupplayerstats.php?id=34146304

Lookup Event
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
Lookup a teams details using its ID. {idEvent}
https://www.thesportsdb.com/api/v1/json/123/lookupevent.php?id=441613

Lookup Event Results
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
Lookup all results for an event using its ID. {idEvent}
https://www.thesportsdb.com/api/v1/json/123/eventresults.php?id=652890

Lookup Event Lineup
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
Lookup the team lineups for an event using its ID. {idEvent}
https://www.thesportsdb.com/api/v1/json/123/lookuplineup.php?id=1032723

Lookup Event Timeline
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
Lookup the timeline for an event using its ID. {idEvent}
https://www.thesportsdb.com/api/v1/json/123/lookuptimeline.php?id=1032718

Lookup Event Statistics
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
Lookup the statistics for an event using its ID. {idEvent}
https://www.thesportsdb.com/api/v1/json/123/lookupeventstats.php?id=1032723

Lookup Event TV Broadcasts
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
Lookup all TV channels showing an event using its ID. {idEvent}
https://www.thesportsdb.com/api/v1/json/123/lookuptv.php?id=584911

Lookup Venue
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
Lookup a team using its ID. {idTeam}
https://www.thesportsdb.com/api/v1/json/123/lookupvenue.php?id=16163


All Sports:
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
List all the sport categories supported on the website.
https://www.thesportsdb.com/api/v1/json/123/all_sports.php

All Countries:
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
List all the geographical countries supported on the website.
https://www.thesportsdb.com/api/v1/json/123/all_countries.php

All Leagues:
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
List all the leagues on TheSportsDB.
https://www.thesportsdb.com/api/v1/json/123/all_leagues.php

List Leagues:
Type:  Parameter:  The country name
Type:  Parameter:  The sport name
Free Limit: limit: Premium Limit: 
Example: JSON Data
List all the leagues in a country for a specific sport. {strCountry} {strSport}
https://www.thesportsdb.com/api/v1/json/123/search_all_leagues.php?c=England&s=Soccer

List Seasons:
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
Example2: JSON Data
Example3: JSON Data
Example4: JSON Data
List all the seasons available for a specific league ID. {idLeague}
https://www.thesportsdb.com/api/v1/json/123/search_all_seasons.php?id=4328
https://www.thesportsdb.com/api/v1/json/123/search_all_seasons.php?id=4328&poster=1
https://www.thesportsdb.com/api/v1/json/123/search_all_seasons.php?id=4328&badge=1
https://www.thesportsdb.com/api/v1/json/123/search_all_seasons.php?id=4328&description=1

List Teams:
Type:  Parameter: 
Type:  Parameter: 
Type:  Parameter: 
Free Limit:  Premium Limit: 
Example: JSON Data
Example2: JSON Data
List all the teams in a specific league by the leagues name, country and sport or ID. {strLeague} {strCountry} {strSport}
https://www.thesportsdb.com/api/v1/json/123/search_all_teams.php?l=English_Premier_League
https://www.thesportsdb.com/api/v1/json/123/search_all_teams.php?s=Soccer&c=Spain

List Players:
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
List all the Players who play for a team by the teams ID. {idTeam}
https://www.thesportsdb.com/api/v1/json/123/lookup_all_players.php?id=133604


Schedule Team Next:
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
See the next few upcoming events for a team using the Team ID. *free key only shows home event
https://www.thesportsdb.com/api/v1/json/123/eventsnext.php?id=133602

Schedule Team Previous:
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
See the last few recent events for a team using the Team ID. *free key only shows home event
https://www.thesportsdb.com/api/v1/json/123/eventslast.php?id=133602

Schedule League Next:
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
See the next upcoming events for a team using the league ID.
https://www.thesportsdb.com/api/v1/json/123/eventsnextleague.php?id=4328

Schedule League Previous:
Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
See the next upcoming events for a team using the league ID. {idLeague}
https://www.thesportsdb.com/api/v1/json/123/eventspastleague.php?id=4328

Schedule Day:
Type:  Parameter: 
Type:  Parameter (Optional): 
Type:  Parameter (Optional): 
Free Limit:  Premium Limit: 
Example: JSON Data
Example2: JSON Data
Example3: JSON Data
Example4: JSON Data
See the events on a specific day in the future, past or present.
You can also add an optional filter for league ID or name. {dateEvent} {strSport} {idLeague}
https://www.thesportsdb.com/api/v1/json/123/eventsday.php?d=2014-10-10
https://www.thesportsdb.com/api/v1/json/123/eventsday.php?d=2014-10-10&s=Baseball
https://www.thesportsdb.com/api/v1/json/123/eventsday.php?d=2014-10-10&l=4424

Schedule Season:
Type:  Parameter:  Type:  Parameter:  Free Limit:  Premium Limit: 
Example: JSON Data
See all the events for a particular season and filter by league ID. {idSeason} {strSeason}
https://www.thesportsdb.com/api/v1/json/123/eventsseason.php?id=4328&s=2014-2015

Schedule TV:
Type:  Parameter:  Type:  Parameter:  Type:  Parameter:  Type:  Parameter:  Type:  Parameter:  Type:  Free Limit:  Premium Limit: 
Example: JSON Data
Example2: JSON Data
Example3: JSON Data
Example4: JSON Data
Example5: JSON Data
See the TV schedual for a particular date. {dateEvent} {strSport} {strCountry} {strChannel} {idChannel}
https://www.thesportsdb.com/api/v1/json/123/eventstv.php?d=2024-07-07
https://www.thesportsdb.com/api/v1/json/123/eventstv.php?d=2018-07-07&s=Fighting
https://www.thesportsdb.com/api/v1/json/123/eventstv.php?d=2019-09-28&a=United_Kingdom&s=Cycling
https://www.thesportsdb.com/api/v1/json/123/eventstv.php?c=Peacock_Premium
https://www.thesportsdb.com/api/v1/json/123/eventstv.php?id=7000


seperator bar


 v1 API Video
The video API allows you to list any YouTube highlights associated with an event.
Please be aware that we have no control of YouTube and some videos may be geolocked to specific countries.
You can use various filters including date on its own or with a League ID or Sport.

Video Youtube Highlights:
Type:  Parameter: 
Type:  Parameter (optional):  {League ID}
Type:  Parameter (optional):  {Sport_Name}
Free Limit:  Premium Limit: 
Example: JSON Data
Example 2: JSON Data
Example 3: JSON Data
See the TV schedual for a particular date
https://www.thesportsdb.com/api/v1/json/123/eventshighlights.php?d=2024-07-07