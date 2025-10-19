<?php
declare(strict_types=1);

/**
 * Copyright 2024, Ioigoume (https://Ioigoume.com.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2024, Ioigoume (https://Ioigoume.com.br)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Ioigoume\OracleDriver\Database\Statement;

use Cake\Database\Statement\BufferedStatement;
use Cake\Database\Statement\BufferResultsTrait;
use Cake\Database\TypeFactory;
use Cake\Database\TypeInterface;
use PDO;

/**
 * Statement class meant to be used by an Oracle driver
 */
use Cake\Database\StatementInterface;

class OracleStatement implements StatementInterface
{
    protected $_statement;
    protected $_driver;
    protected $_bufferResults = false;

    public $queryString;

    public $paramMap;

    /**
     * {@inheritDoc}
     */
    public function execute(?array $params = null): bool
    {
        if ($this->_statement instanceof BufferedStatement) {
            $this->_statement = $this->_statement->getInnerStatement();
        }

        if ($this->_bufferResults) {
            $this->_statement = new OracleBufferedStatement($this->_statement, $this->_driver);
        }

        return $this->_statement->execute($params);
    }

    /**
     * {@inheritDoc}
     */
    public function __get($property)
    {
        if ($property === 'queryString') {
            return empty($this->queryString) ? $this->_statement->queryString : $this->queryString;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function bind(array $params, array $types): void
    {
        if (empty($params)) {
            return;
        }

        $anonymousParams = is_int(key($params));
        $offset = 0;

        foreach ($params as $index => $value) {
            $type = null;
            if (isset($types[$index])) {
                $type = $types[$index];
            }
            if ($anonymousParams) {
                $index += $offset;
            }
            $this->bindValue($index, $value, $type);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function bindValue(string|int $column, mixed $value, string|int|null $type = 'string'): void
    {
        $column = $this->paramMap[$column] ?? $column;

        $type ??= 'string';
        if (!is_int($type)) {
            [$value, $type] = $this->cast($value, $type);
        }

        $this->_statement->bindValue($column, $value, $type);
    }

    /**
     * {@inheritDoc}
     */
    protected function cast(mixed $value, TypeInterface|string|int $type = 'string'): array
    {
        if (is_string($type)) {
            $type = TypeFactory::build($type);
        }
        if ($type instanceof TypeInterface) {
            $value = $type->toDatabase($value, $this->_driver);
            $type = $type->toStatement($value, $this->_driver);
        }

        return [$value, $type];
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(string|int $mode = PDO::FETCH_BOTH): mixed
    {
        if (is_string($mode)) {
            $mode = match (strtolower($mode)) {
                'assoc' => \PDO::FETCH_ASSOC,
                'num' => \PDO::FETCH_NUM,
                'both' => \PDO::FETCH_BOTH,
                default => \PDO::FETCH_BOTH,
            };
        }

        $result = $this->_statement->fetch($mode);
        if (is_array($result)) {
            foreach ($result as $key => &$value) {
                if (is_resource($value)) {
                    $value = stream_get_contents($value);
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchAll(string|int $mode = PDO::FETCH_BOTH): array
    {
        if (is_string($mode)) {
            $mode = match (strtolower($mode)) {
                'assoc' => \PDO::FETCH_ASSOC,
                'num' => \PDO::FETCH_NUM,
                'both' => \PDO::FETCH_BOTH,
                default => \PDO::FETCH_BOTH,
            };
        }

        $result = $this->_statement->fetchAll($mode);
        if (is_array($result)) {
            foreach ($result as $k => $row) {
                foreach ($row as $key => $value) {
                    if (is_resource($value)) {
                        $result[$k][$key] = stream_get_contents($value);
                    }
                }
            }
        }

        return $result;
    }

    public function fetchColumn(int $columnIndex = 0): mixed
    {
        return $this->_statement->fetchColumn($columnIndex);
    }

    public function rowCount(): int
    {
        return $this->_statement->rowCount();
    }

    public function columnCount(): int
    {
        return $this->_statement->columnCount();
    }

    public function closeCursor(): void
    {
        if (method_exists($this->_statement, 'closeCursor')) {
            $this->_statement->closeCursor();
        }
    }

    public function errorCode(): string
    {
        return (string) $this->_statement->errorCode();
    }

    public function errorInfo(): array
    {
        return $this->_statement->errorInfo();
    }

    public function lastInsertId(?string $table = null, ?string $column = null): string|int
    {
        return $this->_statement->lastInsertId($table, $column);
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->fetchAll());
    }

    public function fetchAssoc(): array
    {
        return $this->_statement->fetch(PDO::FETCH_ASSOC);
    }

    public function queryString(): string
    {
        return $this->_statement->queryString ?? '';
    }

    public function getBoundParams(): array
    {
        return $this->paramMap ?? [];
    }

    public function __construct($statement, $driver)
    {
        $this->_statement = $statement;
        $this->_driver = $driver;
    }
}
