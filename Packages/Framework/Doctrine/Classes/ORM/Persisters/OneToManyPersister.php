<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\ORM\Persisters;

use Doctrine\ORM\PersistentCollection,
    Doctrine\ORM\UnitOfWork;

/**
 * Persister for one-to-many collections.
 *
 * IMPORTANT:
 * This persister is only used for uni-directional one-to-many mappings on a foreign key
 * (which are not yet supported). So currently this persister is not used.
 *
 * @since 2.0
 * @author Roman Borschel <roman@code-factory.org>
 * @todo Remove
 */
class OneToManyPersister extends AbstractCollectionPersister
{
    /* Not used for OneToManyPersister */
    protected function _getDeleteRowSQL(PersistentCollection $coll)
    {}

    /* Not used for OneToManyPersister */
    protected function _getInsertRowSQL(PersistentCollection $coll)
    {}

    /* Not used for OneToManyPersister */
    protected function _getUpdateRowSQL(PersistentCollection $coll)
    {}

    /**
     * Generates the SQL UPDATE that updates all the foreign keys to null.
     *
     * @param PersistentCollection $coll
     */
    protected function _getDeleteSQL(PersistentCollection $coll)
    {}

    /**
     * Gets the SQL parameters for the corresponding SQL statement to delete
     * the given collection.
     *
     * @param PersistentCollection $coll
     */
    protected function _getDeleteSQLParameters(PersistentCollection $coll)
    {}

    /**
     * Gets the SQL parameters for the corresponding SQL statement to insert the given
     * element of the given collection into the database.
     *
     * @param PersistentCollection $coll
     * @param mixed $element
     */
    protected function _getInsertRowSQLParameters(PersistentCollection $coll, $element)
    {}

    /**
     * Gets the SQL parameters for the corresponding SQL statement to delete the given
     * element from the given collection.
     *
     * @param PersistentCollection $coll
     * @param mixed $element
     */
    protected function _getDeleteRowSQLParameters(PersistentCollection $coll, $element)
    {}

    /**
     * {@inheritdoc}
     */
    public function count(PersistentCollection $coll)
    {
        $mapping = $coll->getMapping();
        $class = $this->_em->getClassMetadata($mapping['targetEntity']);
        $params = array();
        $id = $this->_em->getUnitOfWork()->getEntityIdentifier($coll->getOwner());

        $where = '';
        foreach ($class->associationMappings[$mapping['mappedBy']]['joinColumns'] AS $joinColumn) {
            if ($where != '') {
                $where .= ' AND ';
            }
            $where .= $joinColumn['name'] . " = ?";
            if ($class->containsForeignIdentifier) {
                $params[] = $id[$class->getFieldForColumn($joinColumn['referencedColumnName'])];
            } else {
                $params[] = $id[$class->fieldNames[$joinColumn['referencedColumnName']]];
            }
        }

        $sql = "SELECT count(*) FROM " . $class->getQuotedTableName($this->_conn->getDatabasePlatform()) . " WHERE " . $where;
        return $this->_conn->fetchColumn($sql, $params);
    }

    /**
     * @param PersistentCollection $coll
     * @param int $offset
     * @param int $length
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function slice(PersistentCollection $coll, $offset, $length = null)
    {
        $mapping = $coll->getMapping();
        return $this->_em->getUnitOfWork()
                  ->getEntityPersister($mapping['targetEntity'])
                  ->getOneToManyCollection($mapping, $coll->getOwner(), $offset, $length);
    }

    /**
     * @param PersistentCollection $coll
     * @param object $element
     */
    public function contains(PersistentCollection $coll, $element)
    {
        $mapping = $coll->getMapping();
        $uow = $this->_em->getUnitOfWork();
        
        // shortcut for new entities
        if ($uow->getEntityState($element, UnitOfWork::STATE_NEW) == UnitOfWork::STATE_NEW) {
            return false;
        }

        // only works with single id identifier entities. Will throw an exception in Entity Persisters
        // if that is not the case for the 'mappedBy' field.
        $id = current( $uow->getEntityIdentifier($coll->getOwner()) );

        return $uow->getEntityPersister($mapping['targetEntity'])
                   ->exists($element, array($mapping['mappedBy'] => $id));
    }
}