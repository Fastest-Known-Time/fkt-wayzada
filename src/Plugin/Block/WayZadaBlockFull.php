<?php

namespace Drupal\fkt_wayzada\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\file\Entity\File;
use Drupal\Core\Url;

/**
 * Provides a block with the WayZada ad in its full version.
 * with elevation profile.
 *
 * @Block(
 *   id = "wayzada_ad_full",
 *   admin_label = @Translation("WayZada Ad - Full"),
 * )
 */
class WayZadaBlockFull extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $output = [];

    $output['#theme'] = 'wayzada_block_full';
    $output['#title'] = '';
    $output['#description'] = '';

    // Construct link to WayZada's site using route name & GPS track URL as URL parameters
    $node = \Drupal::routeMatch()->getParameter('node');

    if ($node->getType() == 'route') {
      $route = $node;
    } else { // otherwise it's an FKT
      $fkt = $node;
      $route = $fkt->get('field_route')->get(0)->entity;
    }

    // Get link to GPS trackfile
    $field_gps_tracks = $node->get('field_gps_track')->getValue();
    if (isset($field_gps_tracks[0]) && is_array($field_gps_tracks[0]) && !empty($field_gps_tracks[0]['target_id'])) {
      // // Handle multiple GPX files
      // foreach ($field_gps_tracks as $trackfile) {
      //   $fid = $trackfile['target_id'];
      //   if ($fid) {
      //     $file = File::load($fid);
      //   }
      // }
      $fid = $field_gps_tracks[0]['target_id'];
      $file = File::load($fid);

      $gpx_url_absolute = file_create_url($file->getFileUri());
      $gpx_url_relative = file_url_transform_relative($gpx_url_absolute);

      // Remove subdomain for local & dev environments
      $pattern = '/^(https?:\/\/)?.*\.fastestknowntime\.com/';
      $replacement = 'https://fastestknowntime.com';
      $gpx_url_absolute = preg_replace($pattern, $replacement, $gpx_url_absolute);
    } else {
      // No GPX; bail.
      return;
    }

    $output['#gpx_url_relative'] = $gpx_url_relative;

    $route_title = $route->getTitle();

    $wayzada_baseurl = 'https://wayzada.com/products/fktproduct';
    $options = [
      'query' => [
        'route' => $route->getTitle(),
        'url' => $gpx_url_absolute,
      ],
    ];
    $wayzada_route_url = Url::fromUri($wayzada_baseurl, $options)->toString();

    $output['#wayzada_route_url'] = $wayzada_route_url;

    $output['#attached'] = [
      'library' => [
        // Attach external WayZada JS libs
        'fkt_wayzada/jscad',
        'fkt_wayzada/lightgl.js',
        'fkt_wayzada/csg.js',
        'fkt_wayzada/proj4js',
        'fkt_wayzada/jsPerf-vincenty',

        // And WayZada custom JS
        'fkt_wayzada/wayzada',

        // And our local JS to fire it off
        'fkt_wayzada/fkt_wayzada'
      ]
    ];

    return $output;
  }

}
