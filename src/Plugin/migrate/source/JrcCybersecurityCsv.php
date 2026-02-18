<?php
declare(strict_types=1);

namespace Drupal\jrc_cybersecurity_taxonomy\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;

/**
 * Source plugin for JRC Cybersecurity Taxonomy CSV data.
 *
 * @MigrateSource(
 *   id = "jrc_cybersecurity_csv",
 *   source_module = "jrc_cybersecurity_taxonomy"
 * )
 */
class JrcCybersecurityCsv extends SourcePluginBase {

  /**
   * The CSV file path.
   *
   * @var string
   */
  protected $csvFilePath;

  /**
   * The CSV rows.
   *
   * @var array
   */
  protected $rows = [];

  /**
   * The header row.
   *
   * @var array
   */
  protected $header = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);

    if (empty($configuration['path'])) {
      throw new \Exception('CSV path is required for jrc_cybersecurity_csv source plugin.');
    }

    // Costruisci il percorso completo del file CSV.
    $root = \Drupal::root();
    $path = $configuration['path'];
    $module_path = \Drupal::service('extension.path.resolver')
      ->getPath('module', 'jrc_cybersecurity_taxonomy');
    if (strpos($path, '%migrate_source_path%') !== FALSE) {
      $path = str_replace('%migrate_source_path%', $module_path . '/migrations/source', $path);
    }

    if (strpos($path, $root) === 0 || (is_string($path) && strlen($path) > 0 && $path[0] === '/')) {
      $this->csvFilePath = $path;
    }
    elseif (preg_match('#^(modules|profiles|sites)/#', $path)) {
      $this->csvFilePath = $root . '/' . $path;
    }
    else {
      $this->csvFilePath = $root . '/' . $module_path . '/migrations/source/' . $path;
    }
    $this->parseCsv();
  }

  /**
   * Parse the CSV file.
   */
  protected function parseCsv() {
    if (!file_exists($this->csvFilePath)) {
      throw new \Exception("CSV file not found: {$this->csvFilePath}");
    }

    $handle = fopen($this->csvFilePath, 'r');
    if ($handle === FALSE) {
      throw new \Exception("Failed to open CSV file: {$this->csvFilePath}");
    }

    $header = fgetcsv($handle);
    if ($header === FALSE) {
      fclose($handle);
      throw new \Exception("Failed to read header from CSV file: {$this->csvFilePath}");
    }

    $this->header = array_map('trim', $header);

    while (($row = fgetcsv($handle)) !== FALSE) {
      $parsed_row = [];
      foreach ($this->header as $index => $column) {
        $value = isset($row[$index]) ? $row[$index] : '';
        $parsed_row[$column] = $value;
      }
      $this->rows[] = $parsed_row;
    }

    fclose($handle);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'uri' => [
        'type' => 'string',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'pref_label' => 'Preferred label (term name)',
      'uri' => 'URI of the concept',
      'notation' => 'Notation/short code',
      'description' => 'Description',
      'alt_labels' => 'Alternative labels (semicolon separated)',
      'in_schemes' => 'Schemes the concept belongs to (semicolon separated)',
      'broader' => 'Broader concepts - parent terms (semicolon separated)',
      'narrower' => 'Narrower concepts - child terms (semicolon separated)',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    return new \ArrayIterator($this->rows);
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return 'jrc_cybersecurity_csv:' . $this->csvFilePath;
  }

  /**
   * {@inheritdoc}
   */
  public function count($refresh = FALSE) {
    return count($this->rows);
  }

}
