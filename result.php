<?php

    error_reporting(0);
    session_start();
    require_once("./twitteroauth/twitteroauth/twitteroauth-master/twitteroauth/twitteroauth.php");
    require_once('./sentiment_analysis/vendor/autoload.php');
    require_once('./mergesort.php');
    require_once('./dates.php');

    use Sentiment\Analyzer;

    interface Comparable {
        public function compareTo($other);
    }

    class Tweet implements Comparable {
        const TOLERANCE = 0.0001;

        public function __construct($sent, $text, $username, $profile_img, $img) {
            $this->tweet_sent = $sent;
            $this->tweet_text = $text;
            $this->username = $username;
            $this->profile_img = $profile_img;
            $this->img = $img;
        }

        public function compareTo($other) {
            $diff = $this->tweet_sent - $other->tweet_sent;
            if ($diff > self::TOLERANCE) {
                return 1;
            }

            elseif ($diff < -self::TOLERANCE) {
                return -1;
            }

            return 0;
        }
    }

    function getConnectionWithAccessToken($cons_key, $cons_secret, $oauth_token, $oauth_token_secret)
    {

        $connection = new TwitterOAuth($cons_key, $cons_secret, $oauth_token, $oauth_token_secret);
        return $connection;

    }

    if (isset($_SESSION['query'])) {
        $twitterkeyword = $_SESSION['query'];
        $notweets = 100;
        $consumerkey = "8UMys8nqP9G5lDpfS5dFL1fQh";
        $consumersecret = "4UCHfteUBfQGXliqZNnIuHybRniJpyC20SdVxCmdG0Ce0OJsxg";
        $accesstoken = "1046313089056952320-sNOR8rwWYPhhSbM25w8WrlPMWD7lmn";
        $accesstokensecret = "PtwwXVCYv8ik8cFCqxImPDkJiFmfSs6N8xcpROhfajz2W";

        $connection = getConnectionWithAccessToken($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);

        $tweets = $connection->get("https://api.twitter.com/1.1/search/tweets.json?q=" . $twitterkeyword ." -RT&count=100&tweet_mode=extended&lang=en&include_entities=true");
        $tweets = json_encode($tweets);
        $tweets = json_decode($tweets, true);
        $tweets = $tweets['statuses'];
        $media_url = [];
        $tweets_text = [];
        $tweets_users = [];
        $profile_imgs = [];
        for ($i = 0; $i < count($tweets); $i++) {
            $url = $tweets[$i]['full_text'];
            $username = $tweets[$i]['user']['name'];
            $profile_img = $tweets[$i]['user']['profile_image_url'];

            if (isset($tweets[$i]['entities']['media'][0]['media_url'])) {
                $media = $tweets[$i]['entities']['media'][0]['media_url'];
                array_push($media_url, $media);
            }

            else {
                array_push($media_url, '');
            }

            array_push($tweets_text, $url);
            array_push($tweets_users, $username);
            array_push($profile_imgs, $profile_img);
        }

        $tweets_sentiment = [];
        $positive = array();
        $negative = array();
        $poscount = 0;
        $negcount = 0;

        $analyzer = new Analyzer();
        for ($i = 0; $i < count($tweets_text); $i++) {
            $text = $tweets_text[$i];
            $result = $analyzer->getSentiment($text);

            $sentiment = $result['pos'] - $result['neg'];
            $tweets_sentiment[$i] = $sentiment;

            if ($sentiment > 0) {
                $array_tweet = new Tweet(round(100 * $sentiment), $tweets_text[$i], $tweets_users[$i], $profile_imgs[$i], $media_url[$i]);
                $positive[$poscount] = $array_tweet;
                $poscount++;
            } else if ($sentiment < 0) {
                $array_tweet = new Tweet(round(100 * $sentiment), $tweets_text[$i], $tweets_users[$i], $profile_imgs[$i], $media_url[$i]);
                $negative[$negcount] = $array_tweet;
                $negcount++;
            }
        }

        $prevtweets = getTweetsSent($twitterkeyword, $consumerkey, $consumersecret, $accesstoken, $accesstokensecret);
        $chartlabels = getChartLabels();

        $prevtweets_html = "";
        $chartlabels_html = "";

        for ($i = 0; $i < count($prevtweets); $i++) {
            $prevtweets_html .= "<input type = 'hidden' class = 'data' value = ".$prevtweets[$i].">";
            $chartlabels_html .= "<input type = 'hidden' class = 'labels' value = '".$chartlabels[$i]."'>";
        }

        $positive = mergesort($positive);
        $negative = mergesort($negative);

        $pos_string = "";
        $neg_string = "";

        for ($i = count($positive) - 1; $i >= 0; $i--) {
            $pos_string .=
                "<div class = 'tweet'>
                    <p class = 'profile'>
                        <img class='pfp' src='" . $positive[$i]->profile_img . "'> 
                        @" . $positive[$i]->username . "
                    </p>
                    <h2 class='tweet-text'>" . $positive[$i]->tweet_text . "</h2>
                    <img class = 'tweet-img' src='".$positive[$i]->img."'>
                    <p style='text-align: right;'>
                        <span class = 'tweet-sent'>+" . $positive[$i]->tweet_sent . " points</span>
                    </p>
                </div>";
        }

        for ($i = 0; $i < count($negative); $i++) {
            $neg_string .=
                "<div class = 'tweet'>
                    <p class = 'profile'>
                        <img class='pfp' src='" . $negative[$i]->profile_img . "'> 
                        @" . $negative[$i]->username . "
                    </p>
                    <h2 class='tweet-text'>" . $negative[$i]->tweet_text . "</h2>
                    <img class = 'tweet-img' src='".$positive[$i]->img."'>
                    <p style='text-align: right;'>
                        <span class = 'tweet-sent'>" . $negative[$i]->tweet_sent . " points</span>
                    </p>
                </div>";
        }

        $total_sentiment = 0;
        $count = 0;

        for ($i = 0; $i < count($tweets_sentiment); $i++) {
            $total_sentiment += round(100 * $tweets_sentiment[$i]);
            if ($tweets_sentiment[$i] != 0) {
                $count++;
            }
        }
        $total_sentiment /= $count;

        $prevtweets_html .= "<input type = 'hidden' class = 'data' value = ".$total_sentiment.">";
        $chartlabels_html .= "<input type = 'hidden' class = 'labels' value = 'Today'>";
        $average = round((array_sum($prevtweets) + $total_sentiment)/8,2);

        $total_sentiment = round($total_sentiment, 2);
    }

?>

<html>

    <head>

        <title>Twitter Sentiment Analysis</title>

        <link rel="icon" type="image/x-icon" href="logo.png"">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha38-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
        <link href="https://fonts.googleapis.com/css?family=Catamaran:200,400,600,700" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400" rel="stylesheet">
        <link href="./css/index.css" rel = "stylesheet">
        <link href="./css/tweets.css" rel = "stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Dosis:800&display=swap" rel="stylesheet">

        <script src = "https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.bundle.min.js"></script>

    </head>

    <body>

        <?php echo $prevtweets_html; echo $chartlabels_html; ?>

        <a href="index.php">
            <div id = "sentiment">

                <div class = "header">

                    <i class="fab fa-twitter-square"></i>
                    <span class = "title">mytwittersent</span>
                    <br>
                    <span class = "subtitle">How is Twitter feeling today?</span>

                </div>

            </div>
        </a>

        <div class = "container">

            <span class = "query-label">Results for: </span>
            <div class = "query"><span><?php echo $_SESSION['query'] ?></span></div>
            <br>

            <hr id = "divider">

            <br>

            <div style = "text-align: right;">
                <span class = "first">Sentiment Analysis</span>

                <br>

                <span class = "second">
                    <?php

                        if (isset($total_sentiment)) {
                            if ($total_sentiment >= 0) {
                                $total_sentiment = str_replace(' ', '', $total_sentiment);
                                echo "<span class = 'second' style = 'color: rgba(255, 251, 244, 0.5);'>+</span>" .
                                    $total_sentiment;
                            }

                            else {
                                $total_sentiment *= -1;
                                $total_sentiment = str_replace(' ', '', $total_sentiment);
                                echo "<span class = 'second' style = 'color: rgba(255, 251, 255, 0.5);'>-</span>".
                                    $total_sentiment;
                            }
                        }

                    ?>
                </span>

                <br>

                <span class = "second" style = 'font-size: 18px;'>
                    <?php

                        echo $average.
                        "<span class = 'second' style = 'font-size: 18px; color: rgba(255, 251, 244, 0.5;'> on average</span>";

                    ?>
                </span>

                <br>

            </div>

            <canvas id = "sentChart" width = "100%" height = "60"></canvas>

            <div style = "text-align: right;">
                <span class = "query-label" style = "font-size: 20px;">Viewing tweets from: </span>

                <div class = "query">
                    <span style = "font-size: 20px;">Today</span>
                </div>
            </div>

            <hr id = "divider">

        </div>

        <br>
        <br>

        <div class = "container-label">
            <?php
                if (isset($pos_string))
                    echo "<span style = 'width: 440px; float: right;'>
                            <h1 class = 'first' style = 'text-align: center; font-size: 33px;'>Positive (".count($positive).")</h1>
                          </span>";
            ?>
        </div>

        <div class = "container-label">
            <?php
                if (isset($neg_string))
                echo "<span style = 'width: 440px; float: left;'>
                            <h1 class = 'first' style = 'text-align: center; font-size: 33px;'>Negative (".count($negative).")</h1>
                          </span>";
            ?>
        </div>

        <div class = "tweet-container">
            <?php
                if (isset($pos_string)) {
                    echo $pos_string;
                }
            ?>
        </div>

        <div id = "neg" class = "tweet-container">
            <?php
                if (isset($neg_string)) {
                    echo $neg_string;
                }
            ?>
        </div>

        <script src = "./js/generatechart.js"></script>

    </body>

</html>
