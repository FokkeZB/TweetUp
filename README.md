# TweetUp
This simple PHP script automagically publishes your [Meetup](http://meetup.com) group's activity on Twitter.

## Quick Start

* Open `config.php` and follow the steps to set it up.
* Open `tweetup.php` in your browser or setup a cronjob like:

<pre>0 * * * * wget -O - -q -t 1 http://www.website.com/tweetup/tweetup.php</pre>

## What & When
The script publishes most of the activity in the activity stream and newly announced meetups. The tweet for the meetup can be set in the config. For now, the texts for the other activities are hard-coded in the script. On the first run, no activities will be published and a `tweetup.last` file will be created holding the time of the newest activity found. On the next run it will publish all newer activies and meetups.

## Dependecies
This script relies on two Github-hosted project that have been [subtree-merged](https://help.github.com/articles/working-with-subtree-merge) into this project for convenience:

* [Meetup](https://github.com/FokkeZB/Meetup): Simple PHP Meetup API client
* [Codebird](https://github.com/mynetx/codebird-php): Great PHP Twitter API client

## License

<pre>
Copyright 2013 Fokke Zandbergen

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
</pre>