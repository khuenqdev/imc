<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * ImageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ImageRepository extends EntityRepository
{
    /**
     * Simple method for finding images
     *
     * @param array $filters
     * @return array
     */
    public function findImages(array $filters = [])
    {
        $qb = $this->createQueryBuilder('i');

        if (isset($filters['only_with_location']) && $filters['only_with_location']) {
            $qb->andWhere('i.latitude IS NOT NULL')
                ->andWhere('i.longitude IS NOT NULL')
                ->andWhere('i.isLocationCorrect = true');
        }

        if (isset($filters['search'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('i.description', $qb->expr()->literal("%{$filters['search']}%")),
                $qb->expr()->like('i.address', $qb->expr()->literal("%{$filters['search']}%"))
            ));
        }

        $this->filterResultsByBoundingBox($qb, $filters);

        if (isset($filters['offset'])) {
            $qb->setFirstResult($filters['offset']);
        }

        if (isset($filters['limit'])) {
            $qb->setMaxResults($filters['limit']);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get unique location coordinates (markers for map)
     *
     * @param array $filters
     * @return array
     */
    public function getLocationCoordinates(array $filters = [])
    {
        $qb = $this->createQueryBuilder('i');

        $qb->select('i.latitude')
            ->addSelect('i.longitude')
            ->where('i.latitude IS NOT NULL')
            ->andWhere('i.longitude IS NOT NULL')
            ->andWhere('i.isLocationCorrect = true');

        $this->filterResultsByBoundingBox($qb, $filters);

        if (isset($filters['offset'])) {
            $qb->setFirstResult($filters['offset']);
        }

        if (isset($filters['limit'])) {
            $qb->setMaxResults($filters['limit']);
        }

        $qb->groupBy('i.latitude')
            ->addGroupBy('i.longitude');

        return $qb->getQuery()->getResult();
    }

    /**
     * Get total number of images
     *
     * @return mixed|null
     */
    public function getNumberOfImages()
    {
        $query = $this->getCountQuery();

        try {
            return $query->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Count number of images within a region based on map bounding box
     *
     * @param array $filters
     * @return mixed|null
     */
    public function getNoOfImageInRegion(array $filters = [])
    {
        $qb = $this->getCountQuery();

        $qb->where('i.latitude IS NOT NULL')
            ->andWhere('i.longitude IS NOT NULL');

        $this->filterResultsByBoundingBox($qb, $filters);

        try {
            return $qb->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $e) {
            return null;
        }
    }

    private function filterResultsByBoundingBox(QueryBuilder &$qb, array $filters = [])
    {
        if (isset($filters['min_lat'])) {
            $qb->andWhere("i.latitude >= {$filters['min_lat']}");
        }

        if (isset($filters['max_lat'])) {
            $qb->andWhere("i.latitude <= {$filters['max_lat']}");
        }

        if (isset($filters['min_lng'])) {
            $qb->andWhere("i.longitude >= {$filters['min_lng']}");
        }

        if (isset($filters['max_lng'])) {
            $qb->andWhere("i.longitude <= {$filters['max_lng']}");
        }
    }

    /**
     * Get number of images with EXIF location metadata
     *
     * @return mixed|null
     */
    public function getNoOfImagesWithExifLocation()
    {
        $query = $this->getCountQuery()
            ->where('i.isExifLocation = true');

        try {
            return $query->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get number of images without EXIF location metadata
     *
     * @return mixed|null
     */
    public function getNoOfImagesWithoutExifLocation()
    {
        $query = $this->getCountQuery()
            ->where('i.isExifLocation = false');

        try {
            return $query->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get number of successful geoparsed images
     *
     * @return mixed|null
     */
    public function getNoOfSuccessfulGeoparsedImages()
    {
        $query = $this->getCountQuery()
            ->where('i.geoparsed = true')
            ->andWhere('i.latitude IS NOT NULL')
            ->andWhere('i.longitude IS NOT NULL')
            ->andWhere('i.isExifLocation = false');

        try {
            return $query->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get number of unsuccessful geoparsed images
     *
     * @return mixed|null
     */
    public function getNoOfUnsuccessfulGeoparsedImages()
    {
        $query = $this->getCountQuery()
            ->where('i.geoparsed = true')
            ->andWhere('i.isExifLocation = false');

        $query->andWhere($query->expr()->orX(
            $query->expr()->isNull('i.latitude'),
            $query->expr()->isNull('i.longitude')
        ));

        try {
            return $query->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get number of correct location images
     *
     * @return mixed|null
     */
    public function getNoOfCorrectLocationImages()
    {
        $query = $this->getCountQuery()
            ->where('i.geoparsed = true')
            ->andWhere('i.latitude IS NOT NULL')
            ->andWhere('i.longitude IS NOT NULL')
            ->andWhere('i.isExifLocation = false')
            ->andWhere('i.isLocationCorrect = true');

        try {
            return $query->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get number of incorrect location images
     *
     * @return mixed|null
     */
    public function getNoOfIncorrectLocationImages()
    {
        $query = $this->getCountQuery()
            ->where('i.geoparsed = true')
            ->andWhere('i.latitude IS NOT NULL')
            ->andWhere('i.longitude IS NOT NULL')
            ->andWhere('i.isExifLocation = false')
            ->andWhere('i.isLocationCorrect = false');

        try {
            return $query->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get number of unverified-location images
     *
     * @return mixed|null
     */
    public function getNoOfUnverifiedLocationImages()
    {
        $query = $this->getCountQuery()
            ->where('i.geoparsed = true')
            ->andWhere('i.latitude IS NOT NULL')
            ->andWhere('i.longitude IS NOT NULL')
            ->andWhere('i.isExifLocation = false')
            ->andWhere('i.isLocationCorrect IS NULL');

        try {
            return $query->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Find an unverified image
     *
     * @param array $ignoredImageIds
     * @return mixed|null
     */
    public function findUnverifiedImage($ignoredImageIds = [])
    {
        $qb = $this->createQueryBuilder('i')
            ->where('i.latitude IS NOT NULL')
            ->andWhere('i.longitude IS NOT NULL')
            ->andWhere('i.geoparsed = true')
            ->andWhere('i.isExifLocation = false')
            ->andWhere('i.isLocationCorrect IS NULL');

        if (!empty($ignoredImageIds)) {
            $qb->andWhere('i.id NOT IN (:ignored_ids)')
                ->setParameter(':ignored_ids', $ignoredImageIds);
        }

        try {
            return $qb->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get count query
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getCountQuery()
    {
        return $this->createQueryBuilder('i')
            ->select('COUNT(i.id)');
    }
}
