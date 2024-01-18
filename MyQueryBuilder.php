<?php
class MyQueryBuilder {
    private string $dbType = '';
    private object $pdo;
    private string $selectBlock = '*';
    private string $table = '';
    private array $whereBlock = [];
    private string $orderBlock = '';
    private string $limitBlock = '';

    public function __construct($config)
    {
        $this->dbType = $config['type'];
        $dsn = $config['type']
            . ':host=' . $config['host']
            . ';dbname=' . $config['db_name'];
        $this->pdo = new PDO($dsn, $config['username'], $config['password']);
    }

    public function select(string $fields): MyQueryBuilder
    {
        $this->selectBlock = $fields;

        return $this;
    }

    public function insert(array $row): MyQueryBuilder
    {
        $columns = implode(', ', array_keys($row));
        $values = implode(', :', array_keys($row));
        $sql = 'INSERT INTO ' . $this->table . ' ('. $columns . ') ' .
            'VALUES (:' . $values . ') ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($row);

        return $this;
    }

    public function update(array $row): MyQueryBuilder
    {
        $sql = 'UPDATE ' . $this->table . ' SET ';
        foreach (array_keys($row) as $val) {
            $sql .= "{$val} = :{$val}, ";
        }
        $sql = rtrim($sql, ', ');
        $sql = $this->addWhereBlockToQuery($sql);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($row);

        return $this;
    }

    public function delete(): MyQueryBuilder
    {
        $sql = 'DELETE FROM ' . $this->table;
        $sql = $this->addWhereBlockToQuery($sql);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $this;
    }

    public function table(string $table): MyQueryBuilder
    {
        $this->table = $table;

        return $this;
    }

    public function where(string $column, string $operator, string $value): MyQueryBuilder
    {
        $this->whereBlock[] = [
            'type' => 'AND',
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];

        return $this;
    }

    public function addWhereBlockToQuery(string $sql): string {
        if (!empty($this->whereBlock)) {
            $sql .= ' WHERE ';
            foreach ($this->whereBlock as $index => $where) {
                if ($index > 0) {
                    $sql .= $where['type'] . ' ';
                }
                $sql .= $where['column'] . ' '
                    . $where['operator'] . ' '
                    . '\'' . $where['value'] . '\'';
            }
        }

        return $sql;
    }

    public function orderBy(string $column, string $direction): MyQueryBuilder
    {
        $this->orderBlock = ' ORDER BY ' . $column . ' ' . $direction;

        return $this;
    }

    public function limit(int $limit) : MyQueryBuilder
    {
        if (in_array(strtolower($this->dbType), ['pgsql', 'mysql'])) {
            $this->limitBlock = ' LIMIT ' . $limit;
        } else {
            $this->limitBlock = 'TOP ' . $limit . ' ';
        }

        return $this;
    }

    public function get(): ?array
    {
        $sql = 'SELECT ';
        if (!empty($this->limitBlock) && (!in_array(strtolower($this->dbType), ['pgsql', 'mysql']))) {
            $sql .= $this->limitBlock;
        }
        $sql .= $this->selectBlock;
        $sql .= ' FROM ' . $this->table;

        $sql = $this->addWhereBlockToQuery($sql);

        $sql .= $this->orderBlock;
        if (!empty($this->limitBlock) && (in_array(strtolower($this->dbType), ['pgsql', 'mysql']))) {
            $sql .= $this->limitBlock;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}