<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\ResourceModel;

use Generator;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Query\Generator as QueryGenerator;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;

/**
 * The class is responsible for obtaining ids for entities
 */
class GetEntityIds
{
    private const BATCH_SIZE = 2500;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var QueryGenerator
     */
    private $batchQueryGenerator;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $primaryColumn;

    /**
     * @var string
     */
    private $whereCondition;

    /**
     * @var array|null
     */
    private $innerJoin;

    /**
     * @param ResourceConnection $resourceConnection
     * @param QueryGenerator $batchQueryGenerator
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        QueryGenerator $batchQueryGenerator
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->batchQueryGenerator = $batchQueryGenerator;
    }

    /**
     * Returns Ids of entity
     *
     * @return Generator
     * @throws LocalizedException
     */
    public function execute(): Generator
    {
        $batchSelectIterator = $this->batchQueryGenerator->generate(
            $this->getPrimaryColumn(),
            $this->getSelect(),
            self::BATCH_SIZE
        );

        foreach ($batchSelectIterator as $select) {
            yield $this->getConnection()->fetchCol($select);
        }
    }

    /**
     * Returns primary column name
     *
     * @return string|null
     */
    private function getPrimaryColumn(): ?string
    {
        return $this->primaryColumn;
    }

    /**
     * Returns select object
     *
     * @return Select
     */
    private function getSelect(): Select
    {
        $select = $this->getConnection()->select()->reset();

        $select->from(
            $this->getConnection()->getTableName($this->getTableName()),
            [$this->getPrimaryColumn()]
        );

        if ($this->getInnerJoin()) {
            foreach ($this->getInnerJoin() as $innerJoin) {
                $select->joinInner(
                    [$innerJoin['table'] => $innerJoin['table']],
                    $innerJoin['condition'],
                    ''
                );
            }
        }

        if ($this->getWhereCondition()) {
            $select->where($this->getWhereCondition());
        }

        return $select->distinct();
    }

    /**
     * Returns connection
     *
     * @return AdapterInterface
     */
    private function getConnection(): AdapterInterface
    {
        if ($this->connection === null) {
            $this->connection = $this->resourceConnection->getConnection();
        }

        return $this->connection;
    }

    /**
     * Returns table name
     *
     * @return string|null
     */
    private function getTableName(): ?string
    {
        return $this->tableName;
    }

    /**
     * Returns inner join condition
     *
     * @return array|null
     */
    private function getInnerJoin(): ?array
    {
        return $this->innerJoin;
    }

    /**
     * Returns where condition
     *
     * @return string|null
     */
    private function getWhereCondition(): ?string
    {
        return $this->whereCondition;
    }

    /**
     * Returns table name
     *
     * @param string $tableName
     *
     * @return void
     */
    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }

    /**
     * Set primary column
     *
     * @param string $primaryColumn
     *
     * @return void
     */
    public function setPrimaryColumn(string $primaryColumn): void
    {
        $this->primaryColumn = $primaryColumn;
    }

    /**
     * Set where condition
     *
     * @param string $whereCondition
     *
     * @return void
     */
    public function setWhereCondition(string $whereCondition): void
    {
        $this->whereCondition = $whereCondition;
    }

    /**
     * Set where condition
     *
     * $condition = [[table => table1, condition => condition1], [table => table2, condition => condition2], ...];
     *
     * @param array $condition
     *
     * @return void
     */
    public function setInnerJoin(array $condition): void
    {
        $this->innerJoin = $condition;
    }
}
