<?php
declare(strict_types=1);

namespace Drupal\jrc_cybersecurity_taxonomy\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\Core\Database\Connection;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Lookup taxonomy terms by URI instead of ID.
 *
 * @MigrateProcessPlugin(
 *   id = "jrc_uri_lookup"
 * )
 */
class JrcUriLookup extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  protected $database;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->database = $database;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database')
    );
  }

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (empty($value)) {
      return NULL;
    }

    $uri = trim($value);

    $tid = $this->database->query("SELECT entity_id FROM {taxonomy_term__field_jrc_uri} WHERE field_jrc_uri_uri = :uri LIMIT 1", [':uri' => $uri])->fetchField();

    if ($tid) {
      return $tid;
    }

    return NULL;
  }

}