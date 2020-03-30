<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

// Use the REST API Client to make requests to the Twilio REST API
use Twilio\Rest\Client;

class RSSNotifier {
  public $cache_dir        = 'cache';
  public $stored_date_file = 'last-posted-date.txt';
  public $notifiers        = [];
  public $twillio          = [];
  public $log              = [];
  public $debug            = false;

  public function getDateFile($notifierKey, $feedKey) {
    return $this->cache_dir . '/' . $notifierKey . '-' . $feedKey . '-' . $this->stored_date_file;
  }

  public function addDebug( $log ) {
    if ( $this->debug ) {
      $this->log[] = '(' . date('n/j/Y g:i:s:va') . ') - ' . $log;
    }
  }

  public function run() {
    $notifications = [];

    $this->addDebug( count($this->notifiers) . ' notifiers found' );

    // Loop through the notifiers
    foreach( $this->notifiers as $notifierKey => $notifier ) {

      $this->addDebug( count($notifier['feeds']) . ' feeds found for ' . $notifierKey );

      // Loop through the notifier feeds
      foreach( $notifier['feeds'] as $feedKey => $rss ) {
        $this->addDebug( 'Fetching ' . $rss );

        // Fetch & parse the RSS feed URL into an array
        $feed = $this->getRSS( $rss );

        $this->addDebug( count( $feed ) . ' feed items found' );

        // Sort the feed array by newest
        usort( $feed, [ $this, 'sortByDate' ] );

        // Check if the feed has a new item
        if ( $this->hasNew( $notifierKey, $feedKey, $feed[0]['date'] ) ) {

          $this->addDebug( 'New item found for ' . $notifierKey . '-'. $feedKey . ': ' . $feed[0]['title'] );

          $message = $feed[0]['title'] . "\r\n\r\n" . $feed[0]['link'] . "\r\n\r\n" . $feed[0]['desc'];
          $message = str_replace(['<br />'], ["\r\n"], $message);
          $message = strip_tags($message);
          $notifications[] = [
            'notifierKey' => $notifierKey,
            'feedKey'     => $feedKey,
            'message'     => $message
          ];

          // Update the last posted date for this feed;
          $date = $feed[0]['date'];
          $file = $this->getDateFile($notifierKey, $feedKey);
          file_put_contents($file, $date);
        } else {
          $this->addDebug( 'No new items found for ' . $notifierKey . '-'. $feedKey . ', current item date: ' . $feed[0]['date'] );
        }
      }
    }

    // Send notifications out
    foreach( $notifications as $key => $notification ) {
      // Loop through the numbers
      foreach($this->notifiers[$notification['notifierKey']]['numbers'] as $key => $number ) {
        if ( $this->sendNotification( $number, $notification['message'] ) ) {
          echo 'Notification sent: ' . $number . ' - ' . $notification['message'];
        } else {
          echo 'Notification failed: ' . $number . ' - ' . $notification['message'];
        }
      }
    }
  }

  public function sendNotification( $phone, $notification ) {
    $client = new Client($this->twillio['sid'], $this->twillio['auth_token']);

    if ( $client->messages->create( $phone, [
      'from' => $this->twillio['number'],
      'body' => $notification
    ]) ) {
      return true;
    }

    return false;
  }

  public function getRSS( $rss_url ) {
    $rss = new DOMDocument();

    // Fetch the RSS feed
    $rss->load( $rss_url );

    // Parse the RSS feed into an array
    $feed = [];
    foreach( $rss->getElementsByTagName('item') as $node ) {
      $item = array (
        'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
        'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
        'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
        'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
        );
      array_push($feed, $item);
    }

    return $feed;
  }

  private function hasNew( $notifier_key, $feed_key, $newest_date ) {
    $cache_file = $this->getDateFile($notifier_key, $feed_key);

    if ( file_exists( $cache_file  ) ) {
      $cached_date = trim(file_get_contents( $cache_file ));

      $this->addDebug( 'Cache filed found: ' . $cache_file );
      $this->addDebug( 'Newest item date: ' . $newest_date );
      $this->addDebug( 'Cached item date: ' . $cached_date );

      if ( strtotime($newest_date) > strtotime($cached_date) ) {
        return true;
      }

      return false;
    }

    return true;
  }

  private function sortByDate($a, $b) {
    $t1 = strtotime($b['date']);
    $t2 = strtotime($a['date']);

    return $t1 - $t2;
  }
}

$RSSNotifier = new RSSNotifier;
$RSSNotifier->twillio = [
  'sid'        => 'AC692f8606b105e8135d3fdfca2213fa44',
  'auth_token' => 'bb1efaa5a09a5ab32a8ca834f9afbd51',
  'number'     => '+12062037839'
];
$RSSNotifier->notifiers = [
  'ben' => [
    'numbers' => ['+14692782695'],
    'feeds'   => [
      'upwork_domestic' => 'https://www.upwork.com/ab/feed/topics/rss?securityToken=4f829a7a1576cb2283d09961c1afdcb9ff8bb8ca6f03ebcda17b501df0795a4d1c305aa1bd67522a773253a1b245b749f333bf1bdc51707a82da2ce2023a00dd&userUid=950402002326204416&orgUid=950402002330398721&sort=local_jobs_on_top&topic=domestic',
      'upwork_recommended' => 'https://www.upwork.com/ab/feed/topics/rss?securityToken=4f829a7a1576cb2283d09961c1afdcb9ff8bb8ca6f03ebcda17b501df0795a4d1c305aa1bd67522a773253a1b245b749f333bf1bdc51707a82da2ce2023a00dd&userUid=950402002326204416&orgUid=950402002330398721&sort=local_jobs_on_top&topic=recommended'
    ]
  ]
];

if ( ! empty( $_REQUEST['debug'] ) ) {
  $RSSNotifier->debug = true;
}

$RSSNotifier->run();

if ( $RSSNotifier->debug ) {
  echo '<ol>';
  foreach( $RSSNotifier->log as $key => $log ) {
    echo '<li>' . $log . '</li>';
  }
  echo '</ol>';
}
