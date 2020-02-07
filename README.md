# PDONoSQL
A bunch of classes in _PHP_ based on a PDO connection to communicate with a SQL database without writing any query.

- the main namespace is **pdonosql** having as sub-namespaces: **condition\check** (for input+ouput checkers) and **condition\bag** (for conditions container/bag), **utils** (containing an autoload file and the helpful class **Utils** with static methods)
- the important class is **pdonosql\PDONoSQL**
- see (and execute) 'pdo_nosql.test.php' for how it works.

Because it uses types parameters, it depends on PHP7. This is for integrity and ease of use.
With the help of intellisense, you have access to all the information needed without exploring the module.

Don't hesitate to notice me about any issue, so that I can fix it quickly.
Good luck !

