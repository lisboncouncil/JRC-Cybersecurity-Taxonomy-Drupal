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
 * Determine category type from in_schemes field.
 *
 * @MigrateProcessPlugin(
 *   id = "jrc_category_from_schemes"
 * )
 */
class JrcCategoryFromSchemes extends ProcessPluginBase implements ContainerFactoryPluginInterface {

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

    $schemes = is_array($value) ? $value : [$value];

    $category_map = [
      'http://data.jrc.ec.europa.eu/ontology/cybersecurity/knowledge_domain' => 'Knowledge Domain',
      'http://data.jrc.ec.europa.eu/ontology/cybersecurity/sector' => 'Sector',
      'http://data.jrc.ec.europa.eu/ontology/cybersecurity/technology' => 'Technology',
      'http://data.jrc.ec.europa.eu/ontology/cybersecurity/use_case' => 'Use Case',
    ];

    foreach ($schemes as $scheme) {
      $scheme = trim($scheme);
      if (isset($category_map[$scheme])) {
        $category_name = $category_map[$scheme];
        $tid = $this->database->query("SELECT tid FROM {taxonomy_term_field_data} WHERE vid = :vid AND name = :name LIMIT 1", [':vid' => 'jrc_cybersecurity_category', ':name' => $category_name])->fetchField();
        
        if ($tid) {
          return $tid;
        }
      }
    }

    return NULL;
  }

}