<?php
namespace TYPO3\FLOW3\Cache\Backend;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * A PDO database cache backend
 *
 * @api
 * @scope prototype
 */
class PdoBackend extends \TYPO3\FLOW3\Cache\Backend\AbstractBackend {

	/**
	 * @var string
	 */
	protected $dataSourceName;

	/**
	 * @var string
	 */
	protected $username;

	/**
	 * @var string
	 */
	protected $password;

	/**
	 * @var \PDO
	 */
	protected $databaseHandle;

	/**
	 * @var string
	 */
	protected $pdoDriver;

	/**
	 * Sets the DSN to use
	 *
	 * @param string $DSN The DSN to use for connecting to the DB
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @api
	 */
	public function setDataSourceName($DSN) {
		$this->dataSourceName = $DSN;
	}

	/**
	 * Sets the username to use
	 *
	 * @param string $username The username to use for connecting to the DB
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @api
	 */
	public function setUsername($username) {
		$this->username = $username;
	}

	/**
	 * Sets the password to use
	 *
	 * @param string $password The password to use for connecting to the DB
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @api
	 */
	public function setPassword($password) {
		$this->password = $password;
	}

	/**
	 * Initialize the cache backend.
	 *
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @return void
	 */
	public function initializeObject() {
		$this->connect();
	}

	/**
	 * Saves data in the cache.
	 *
	 * @param string $entryIdentifier An identifier for this specific cache entry
	 * @param string $data The data to be stored
	 * @param array $tags Tags to associate with this cache entry
	 * @param integer $lifetime Lifetime of this cache entry in seconds. If NULL is specified, the default lifetime is used. "0" means unlimited liftime.
	 * @return void
	 * @throws \TYPO3\FLOW3\Cache\Exception if no cache frontend has been set.
	 * @throws \InvalidArgumentException if the identifier is not valid
	 * @throws \TYPO3\FLOW3\Cache\Exception\InvalidDataException if $data is not a string
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @api
	 */
	public function set($entryIdentifier, $data, array $tags = array(), $lifetime = NULL) {
		if (!$this->cache instanceof \TYPO3\FLOW3\Cache\Frontend\FrontendInterface) throw new \TYPO3\FLOW3\Cache\Exception('No cache frontend has been set yet via setCache().', 1259515600);
		if (!is_string($data)) throw new \TYPO3\FLOW3\Cache\Exception\InvalidDataException('The specified data is of type "' . gettype($data) . '" but a string is expected.', 1259515601);

		if ($this->has($entryIdentifier)) {
			$this->remove($entryIdentifier);
		}

		$lifetime = ($lifetime === NULL) ? $this->defaultLifetime : $lifetime;

		$statementHandle = $this->databaseHandle->prepare('INSERT INTO "cache" ("identifier", "context", "cache", "created", "lifetime", "content") VALUES (?, ?, ?, ?, ?, ?)');
		$result = $statementHandle->execute(array($entryIdentifier, $this->context, $this->cacheIdentifier, time(), $lifetime, $data));
		if ($result === FALSE) throw new \TYPO3\FLOW3\Cache\Exception('The cache entry "' . $entryIdentifier . '" could not be written.', 1259530791);

		$statementHandle = $this->databaseHandle->prepare('INSERT INTO "tags" ("identifier", "context", "cache", "tag") VALUES (?, ?, ?, ?)');
		foreach ($tags as $tag) {
			$result = $statementHandle->execute(array($entryIdentifier, $this->context, $this->cacheIdentifier, $tag));
			if ($result === FALSE) throw new \TYPO3\FLOW3\Cache\Exception('The tag "' . $tag . ' for cache entry "' . $entryIdentifier . '" could not be written.', 1259530751);
		}
	}

	/**
	 * Loads data from the cache.
	 *
	 * @param string $entryIdentifier An identifier which describes the cache entry to load
	 * @return mixed The cache entry's content as a string or FALSE if the cache entry could not be loaded
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @api
	 */
	public function get($entryIdentifier) {
		$statementHandle = $this->databaseHandle->prepare('SELECT "content" FROM "cache" WHERE "identifier"=? AND "context"=? AND "cache"=?' . $this->getNotExpiredStatement());
		$statementHandle->execute(array($entryIdentifier, $this->context, $this->cacheIdentifier));
		return $statementHandle->fetchColumn();
	}

	/**
	 * Checks if a cache entry with the specified identifier exists.
	 *
	 * @param string $entryIdentifier An identifier specifying the cache entry
	 * @return boolean TRUE if such an entry exists, FALSE if not
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @api
	 */
	public function has($entryIdentifier) {
		$statementHandle = $this->databaseHandle->prepare('SELECT COUNT("identifier") FROM "cache" WHERE "identifier"=? AND "context"=? AND "cache"=?' . $this->getNotExpiredStatement());
		$statementHandle->execute(array($entryIdentifier, $this->context, $this->cacheIdentifier));
		return ($statementHandle->fetchColumn() > 0);
	}

	/**
	 * Removes all cache entries matching the specified identifier.
	 * Usually this only affects one entry but if - for what reason ever -
	 * old entries for the identifier still exist, they are removed as well.
	 *
	 * @param string $entryIdentifier Specifies the cache entry to remove
	 * @return boolean TRUE if (at least) an entry could be removed or FALSE if no entry was found
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @api
	 */
	public function remove($entryIdentifier) {
		$statementHandle = $this->databaseHandle->prepare('DELETE FROM "tags" WHERE "identifier"=? AND "context"=? AND "cache"=?');
		$statementHandle->execute(array($entryIdentifier, $this->context, $this->cacheIdentifier));

		$statementHandle = $this->databaseHandle->prepare('DELETE FROM "cache" WHERE "identifier"=? AND "context"=? AND "cache"=?');
		$statementHandle->execute(array($entryIdentifier, $this->context, $this->cacheIdentifier));

		return ($statementHandle->rowCount() > 0);
	}

	/**
	 * Removes all cache entries of this cache.
	 *
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @api
	 */
	public function flush() {
		$statementHandle = $this->databaseHandle->prepare('DELETE FROM "tags" WHERE "context"=? AND "cache"=?');
		$statementHandle->execute(array($this->context, $this->cacheIdentifier));

		$statementHandle = $this->databaseHandle->prepare('DELETE FROM "cache" WHERE "context"=? AND "cache"=?');
		$statementHandle->execute(array($this->context, $this->cacheIdentifier));
	}

	/**
	 * Removes all cache entries of this cache which are tagged by the specified tag.
	 *
	 * @param string $tag The tag the entries must have
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function flushByTag($tag) {
		$statementHandle = $this->databaseHandle->prepare('DELETE FROM "cache" WHERE "context"=? AND "cache"=? AND "identifier" IN (SELECT "identifier" FROM "tags" WHERE "context"=? AND "cache"=? AND "tag"=?)');
		$statementHandle->execute(array($this->context, $this->cacheIdentifier,$this->context, $this->cacheIdentifier, $tag));

		$statementHandle = $this->databaseHandle->prepare('DELETE FROM "tags" WHERE "context"=? AND "cache"=? AND "tag"=?');
		$statementHandle->execute(array($this->context, $this->cacheIdentifier, $tag));
	}

	/**
	 * Finds and returns all cache entry identifiers which are tagged by the
	 * specified tag.
	 *
	 * @param string $tag The tag to search for
	 * @return array An array with identifiers of all matching entries. An empty array if no entries matched
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @api
	 */
	public function findIdentifiersByTag($tag) {
		$statementHandle = $this->databaseHandle->prepare('SELECT "identifier" FROM "tags" WHERE "context"=?  AND "cache"=? AND "tag"=?');// . $this->getNotExpiredStatement());
		$statementHandle->execute(array($this->context, $this->cacheIdentifier, $tag));
		return $statementHandle->fetchAll(\PDO::FETCH_COLUMN);
	}

	/**
	 * Does garbage collection
	 *
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @api
	 */
	public function collectGarbage() {
		$statementHandle = $this->databaseHandle->prepare('DELETE FROM "tags" WHERE "context"=? AND "cache"=? AND "identifier" IN (SELECT "identifier" FROM "cache" WHERE "context"=? AND "cache"=? AND "lifetime" > 0 AND "created" + "lifetime" < ' . time() . ')');
		$statementHandle->execute(array($this->context, $this->cacheIdentifier, $this->context, $this->cacheIdentifier));

		$statementHandle = $this->databaseHandle->prepare('DELETE FROM "cache" WHERE "context"=? AND "cache"=? AND "lifetime" > 0 AND "created" + "lifetime" < ' . time());
		$statementHandle->execute(array($this->context, $this->cacheIdentifier));
	}

	/**
	 * Returns an SQL statement that evaluates to true if the entry is not expired.
	 *
	 * @return string
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function getNotExpiredStatement() {
		return ' AND ("lifetime" = 0 OR "created" + "lifetime" >= ' . time() . ')';
	}

	/**
	 * Connect to the database
	 *
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function connect() {
		try {
			$splitdsn = explode(':', $this->dataSourceName, 2);
			$this->pdoDriver = $splitdsn[0];

			if ($this->pdoDriver === 'sqlite' && !file_exists($splitdsn[1])) {
				$this->databaseHandle = new \PDO($this->dataSourceName, $this->username, $this->password);
				$this->createCacheTables();
			} else {
				$this->databaseHandle = new \PDO($this->dataSourceName, $this->username, $this->password);
			}
			$this->databaseHandle->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

			if ($this->pdoDriver === 'mysql') {
				$this->databaseHandle->exec('SET SESSION sql_mode=\'ANSI\';');
			}
		} catch (\PDOException $e) {}
	}

	/**
	 * Creates the tables needed for the cache backend.
	 *
	 * @return void
	 * @throws \TYPO3\FLOW3\Persistence\Exception if something goes wrong
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function createCacheTables() {
		try {
			\TYPO3\FLOW3\Utility\PdoHelper::importSql($this->databaseHandle, $this->pdoDriver, FLOW3_PATH_FLOW3 . 'Resources/Private/Cache/SQL/DDL.sql');
		} catch (\PDOException $e) {
			throw new \TYPO3\FLOW3\Persistence\Exception('Could not create cache tables with DSN "' . $this->dataSourceName . '". PDO error: ' . $e->getMessage(), 1259576985);
		}
	}
}
?>