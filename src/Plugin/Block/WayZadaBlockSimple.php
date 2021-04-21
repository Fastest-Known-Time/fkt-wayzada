<?php

namespace Drupal\fkt_wayzada\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\file\Entity\File;
use Drupal\Core\Url;

/**
 * Provides a block with the WayZada ad in its simple/minimal version:
 * just the ad, no elevation profile.
 *
 * @Block(
 *   id = "wayzada_ad_simple",
 *   admin_label = @Translation("WayZada Ad - Simple"),
 *   context = {
 *     "node" = @ContextDefinition(
 *       "entity:node",
 *       label = @Translation("Current Node")
 *     )
 *   }
 * )
 */
class WayZadaBlockSimple extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $output = [];

    $output['#theme'] = 'wayzada_block_simple';
    $output['#title'] = '';
    $output['#description'] = '';

    // Get the node on which this block is being displayed
    // using block context system, mostly to take care of cacheability.
    // Requires the context annotation in @Block comment above.
    $node = $this->getContextValue('node');

    // Construct link to WayZada's site using route name & GPS track URL as URL parameters
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

      // Remove subdomain for local & dev environments
      $pattern = '/^(https?:\/\/)?.*\.fastestknowntime\.com/';
      $replacement = 'https://fastestknowntime.com';
      $gpx_url_absolute = preg_replace($pattern, $replacement, $$gpx_url_absolute);
    } else {
      // No GPX; bail.
      return;
    }

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

    return $output;
  }

}
