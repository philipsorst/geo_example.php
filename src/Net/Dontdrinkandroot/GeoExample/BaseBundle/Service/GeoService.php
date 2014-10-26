<?php


namespace Net\Dontdrinkandroot\GeoExample\BaseBundle\Service;

use Doctrine\DBAL\Connection;

class GeoService
{

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function search($searchString)
    {

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder = $queryBuilder
            ->select('id', 'name')
            ->from('city', 'city')
            ->where('city.name LIKE :searchname')
            ->setParameter('searchname', $searchString . '%');
        $rows = $queryBuilder->execute()->fetchAll();

        return $rows;
    }

    public function getCity($id)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder = $queryBuilder
//            ->select('c.id', 'c.name', 'c.lat', 'c.long')
            ->select(
                'c.id AS id, c.name AS name, c.lat AS lat, c.lng AS lng, s.name AS state_name, ct.name AS county_name'
            )
            ->from('city', 'c')
            ->innerJoin('c', 'state', 's', 'c.state_id = s.id')
            ->leftJoin('c', 'county', 'ct', 'c.county_id = ct.id')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->setFirstResult(0)
            ->setMaxResults(1);
        $rows = $queryBuilder->execute()->fetchAll();

        return $rows[0];
    }

    public function findNearbyCities($lat, $lng, $maxDistance)
    {
        // @formatter:off
        $sql = 'SELECT ' .
            'id, name, ( ' .
                '6371 * acos ( ' .
                    'cos ( radians(:lat) ) ' .
                    '* cos( radians( lat ) ) ' .
                    '* cos( radians( lng ) - radians(:lng) ) ' .
                    '+ sin ( radians(:lat) ) ' .
                    '* sin( radians( lat ) ) ' .
                ') ' .
            ') AS distance ' .
            'FROM city ' .
            'HAVING distance < :maxDistance ' .
            'ORDER BY distance ASC ';
//            'LIMIT 0 , 20;';
        // @formatter:on
        $rows = $this->connection->fetchAll($sql, ['lat' => $lat, 'lng' => $lng, 'maxDistance' => $maxDistance]);

        return $rows;
    }
}