<?php

    require_once("./twitteroauth/twitteroauth/twitteroauth-master/twitteroauth/twitteroauth.php");
    require_once('./sentiment_analysis/vendor/autoload.php');

    use Sentiment\Analyzer;

    function getTweetsSent($key, $consumerkey, $consumersecret, $accesstoken, $accesstokensecret) {
        $connection = getConnectionWithAccessToken($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);
        $date = [];
        $date[0] = date('Y-m-d');

        for ($i = 1; $i < 8; $i++) {
            $d = strtotime($date[$i-1].' yesterday');
            $date[$i] = date('Y-m-d',$d);
        }

        $tweet_id = [];

        for ($i = 7; $i >= 0; $i--) {
            $tweets = $connection->
                get("https://api.twitter.com/1.1/search/tweets.json?q=".
                    $key.
                    " -RT&result_type=recent&until=".$date[$i]."&lang=en&include_entities=true");
            $tweets = json_encode($tweets);
            $tweets = json_decode($tweets, true);
            $tweets = $tweets['statuses'];
            $id = $tweets[0]['id'];
            array_push($tweet_id, $id);
        }

        $tweets_sent = [];

        for ($i = 0; $i < count($tweet_id)-1; $i++) {
            $tweetsbyday = $connection->
                get("https://api.twitter.com/1.1/search/tweets.json?q=".
                    $key.
                    " -RT&count=100&tweet_mode=extended&lang=en&include_entities=true
                    &since_id=".$tweet_id[$i]."&max_id=".$tweet_id[$i+1]);

            $tweetsbyday = json_encode($tweetsbyday);
            $tweetsbyday = json_decode($tweetsbyday, true);
            $tweetsbyday = $tweetsbyday['statuses'];
            $tweets_text = [];

            for ($j = 0; $j < count($tweetsbyday); $j++) {
                $url = $tweetsbyday[$j]['full_text'];
                array_push($tweets_text, $url);
            }

            $analyzer = new Analyzer();
            $total_sentiment = 0;
            for ($j = 0; $j < count($tweets_text); $j++) {
                $text = $tweets_text[$j];
                $result = $analyzer->getSentiment($text);

                $total_sentiment += $result['pos'] - $result['neg'];
            }

            $total_sentiment /= count($tweets_text);
            $total_sentiment *= 100;
            $tweets_sent[$i] = $total_sentiment;
        }

        return $tweets_sent;
    }

    function getChartLabels() {

        $date = [];
        $date[0] = date('Y-m-d');

        for ($i = 1; $i < 8; $i++) {
            $d = strtotime($date[$i-1].' yesterday');
            $date[$i] = date('Y-m-d',$d);
        }

        $formatted = [];

        for ($i = count($date) - 1; $i >= 1; $i--) {
            $format_date = date("l m-d", strtotime($date[$i]));
            array_push($formatted, $format_date);
        }

        return $formatted;

    }