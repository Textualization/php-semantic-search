# PHP Semantic Search Classes

This classes contain interfaces for semantic search and implementations for them using [Ropherta](https://packagist.org/packages/textualization/ropherta) as embedder and [SQLite3 Vector Search](https://github.com/asg017/sqlite-vss/) as vector database. A keyword search using [SQLite3 FTS4](https://www.sqlite.org/fts3.html) and [BM25](https://en.wikipedia.org/wiki/Okapi_BM25) is also provided.

To populate the vector database, the `Ingester` class contains a recursive chunker similar to the one available in [LangChain](https://python.langchain.com/docs/modules/data_connection/document_transformers/text_splitters/recursive_text_splitter) but with some speed ups plus the ability to refer back to offsets in the source document.

More advanced uses include using vector search as reranker and using [HyDE](https://arxiv.org/abs/2212.10496) to obtain symmetric embeddings.

A demo source documents and indexes over 35,000 documents from StackOverflow and PHP documentation is available [for download](http://textualization.com/download/phpsemsearch_0.1.tar.bz2).


## Document format for ingestion

The ingestion component takes documents in `JSONL` format, with one well-formed JSON document per line.

The text ought to be plain text UTF-8 encoded (per JSON spec). The system will split it into chunks on ingestion.

```
{ "url": "...", "title": "...", "text": "...", "section": "...", "license": "..." }
```

## Vector Index depencencies

### php.ini

Set 

```ini
[sqlite3]
; Directory pointing to SQLite3 extensions
; https://php.net/sqlite3.extension-dir
sqlite3.extension_dir = /path/to/the/sqlite3-vss-dlls
```

### sqlite3-vss dependencies

```bash
apt install libgomp1 libblas3 liblapack3
```

### Ropherta dependencies

Install the ONNX framework:

```
composer exec -- php -r "require 'vendor/autoload.php'; OnnxRuntime\Vendor::check();"
```

and download the Sentence RoBERTa ONNX model (this takes a while, the model is 362Mb in size):

```
composer exec -- php -r "require 'vendor/autoload.php'; Textualization\SentenceTransphormers\Vendor::check();"
```



## Example

Install the SQLite3 extension and Ropherta per the instructions above.

Download the data and indexes from http://textualization.com/download/phpsemsearch_0.1.tar.bz2 (192Mb).

Decompress and move the files `vector.db` and `keyword.db` to the root folder.

### Keyword search

The top documents seem pretty apt.

```bash
$ composer run-script query keyword "composer produce time-out on script execution" 
> Composer\Config::disableProcessTimeout
> @php scripts/query.php 'keyword' 'composer produce time-out on script execution'
Search query: compos OR produc OR time OR out OR on OR script OR execut
0. SearchResult[16.874319996013,https://www.php.net/manual/en/function.set-time-limit.php 0, 'function set-time-limit']
1. SearchResult[16.699065789091,https://stackoverflow.com/q/365496 0, 'How to keep a php script from timing out because of a long mysql query']
2. SearchResult[15.984700996733,https://stackoverflow.com/q/2439087 0, 'How can I set time limit on get_file_contents in PHP?']
3. SearchResult[15.288043063863,https://stackoverflow.com/q/2443491 0, 'What is considered a long execution time?']
4. SearchResult[14.946966486107,https://stackoverflow.com/q/2690504 0, 'PHP: producing relative date/time from timestamps']
5. SearchResult[14.820039567114,https://stackoverflow.com/q/13955436 0, 'Resume PHP to execution script after exception']
6. SearchResult[14.410651129421,https://stackoverflow.com/q/14040241 0, 'Is there any way I can execute a PHP script from MySQL?']
7. SearchResult[14.406441765697,https://stackoverflow.com/q/3110235 0, 'php garbage collection while script running']
8. SearchResult[14.175007211438,https://stackoverflow.com/q/4765107 0, 'Can you figure out this PHP timing issue?']
9. SearchResult[13.918155042184,https://www.php.net/manual/en/function.time-sleep-until.php 0, 'function time-sleep-until']
10. SearchResult[13.677310963332,https://stackoverflow.com/q/2202355 0, 'Set maximum execution time for exec() specifically']
11. SearchResult[13.537783397747,https://stackoverflow.com/q/1194857 0, 'How to schedule the execution of a PHP script on the server side?']
12. SearchResult[13.527986155755,https://stackoverflow.com/q/2128619 0, 'Run Java class file from PHP script on a website']
13. SearchResult[13.371804613541,https://stackoverflow.com/q/11786734 0, 'PHP readfile() and large files']
14. SearchResult[13.354790819156,https://stackoverflow.com/q/5947345 0, 'PHP backup script timing out']
15. SearchResult[13.336357456153,https://stackoverflow.com/q/22817670 0, 'PHP: maximum execution time when importing .SQL data file']
16. SearchResult[13.238850536268,https://stackoverflow.com/q/4501876 0, 'How to get the execution time of a MySQL query from PHP?']
17. SearchResult[13.181655818318,https://stackoverflow.com/q/145402 0, 'Can you recommend Performance Analysis tools for PHP?']
18. SearchResult[13.143897483182,https://stackoverflow.com/q/20907382 0, 'PHP timeout running MSSQL SP']
19. SearchResult[13.044314476608,https://stackoverflow.com/q/5317315 0, 'executing a Powershell script from php']
20. SearchResult[12.948364731457,https://www.php.net/manual/en/function.register-shutdown-function.php 0, 'function register-shutdown-function']
21. SearchResult[12.948078367259,https://stackoverflow.com/q/437716 0, 'High-quality PDF to Word conversion in PHP?']
22. SearchResult[12.923362029257,https://stackoverflow.com/q/16699549 0, 'What happens if you edit a php script while an instance of it is running?']
23. SearchResult[12.914359255007,https://stackoverflow.com/q/9468332 0, 'How can I find out which PHP script a process is running in Linux?']
24. SearchResult[12.897400839699,https://stackoverflow.com/q/6263443 0, 'PDO Connection Test']
25. SearchResult[12.860367898878,https://stackoverflow.com/q/60232298 0, 'In PHP: what is the difference between "return", "yield", "yield from" and mixing both yield and return in same function?']
26. SearchResult[12.813708107511,https://stackoverflow.com/q/9562124 0, 'PHP MySQL set Connection Timeout']
27. SearchResult[12.763582642154,https://stackoverflow.com/q/5638484 0, 'How can I set the maximum execution time for a PHP script?']
28. SearchResult[12.564669201144,https://stackoverflow.com/q/7036767 0, 'How can I get infinite maximum execution time with PHP?']
29. SearchResult[12.552748367252,https://stackoverflow.com/q/8562398 0, 'Get max_execution_time in PHP script']
30. SearchResult[12.457255454068,https://stackoverflow.com/q/4869611 0, 'Fatal error: Maximum execution time of 0 seconds exceeded']
31. SearchResult[12.376381360159,https://stackoverflow.com/q/15776400 0, 'make script execution to unlimited']
32. SearchResult[12.369739483738,https://www.php.net/manual/en/features.commandline.usage.php 0, 'features commandline usage']
33. SearchResult[12.301485848315,https://stackoverflow.com/q/13751772 0, 'PHP output buffer not flushing']
34. SearchResult[12.287292912835,https://www.php.net/manual/en/luasandbox.pauseusagetimer.php 0, 'luasandbox pauseusagetimer']
35. SearchResult[12.274118332228,https://stackoverflow.com/q/16933606 0, 'error_reporting(E_ALL) does not produce an error']
36. SearchResult[12.216750396196,https://stackoverflow.com/q/13352388 0, 'Running php script as cron job - timeout issues?']
37. SearchResult[12.20860917016,https://stackoverflow.com/q/7680572 0, 'Fatal error: Maximum execution time of 300 seconds exceeded']
38. SearchResult[12.174936958789,https://www.php.net/manual/en/features.file-upload.common-pitfalls.php 0, 'features file-upload common-pitfalls']
39. SearchResult[12.159400224308,https://stackoverflow.com/q/9087883 0, 'Reading a Git commit message from PHP']
40. SearchResult[12.098897569281,https://stackoverflow.com/q/740954 0, 'Does sleep time count for execution time limit?']
41. SearchResult[11.979302316362,https://stackoverflow.com/q/20471736 0, 'Gateway Time-out:The gateway did not receive a timely response from the upstream server']
42. SearchResult[11.910314436649,https://stackoverflow.com/q/535020 0, 'Tracking the script execution time in PHP']
43. SearchResult[11.907291503573,https://stackoverflow.com/q/6853057 0, 'Best way to periodically execute a PHP script?']
44. SearchResult[11.825435975279,https://stackoverflow.com/q/17014531 0, 'PHP: Calling MySQL Stored Procedure with Both INPUT AND OUTPUT Parameters (NOT "INOUT")']
45. SearchResult[11.812067390316,https://www.php.net/manual/en/function.getlastmod.php 0, 'function getlastmod']
46. SearchResult[11.758600404801,https://stackoverflow.com/q/4717167 0, 'How to keep a persistent PHP script running?']
47. SearchResult[11.728801536779,https://stackoverflow.com/q/845021 0, 'How can I get useful error messages in PHP?']
48. SearchResult[11.647137530513,https://stackoverflow.com/q/6965541 0, 'Running PHP code/scripts on the command line']
49. SearchResult[11.603155219238,https://stackoverflow.com/q/35681947 0, 'How to get PHP max_execution_time from the command line?']
```


### Vector search

Not as good as keyword but results are really different. Vector search using a symmetric embedder (like Sentence RoBERTa) works better when searching similar documents not queries against documents. For such cases, HyDE (presented below) is better. Alternative, asymmetric embedders (like InstructOR) can be ported to PHP through ONNX.

```bash
$ composer run-script query vector "How to stop composer giving a time-out on a script execution" 
> Composer\Config::disableProcessTimeout
> @php scripts/query.php 'vector' 'How to stop composer giving a time-out on a script execution'
0. SearchResult[-0.51090812683105,https://www.php.net/manual/en/function.oci-bind-by-name.php 14, 'function oci-bind-by-name']
1. SearchResult[-0.5215368270874,https://www.php.net/manual/en/parle.examples.parser.php 5, 'parle examples parser']
2. SearchResult[-0.52460688352585,https://www.php.net/manual/en/mysqli-result.fetch-fields.php 4, 'mysqli-result fetch-fields']
3. SearchResult[-0.54987502098083,https://www.php.net/manual/en/function.stream-filter-register.php 3, 'function stream-filter-register']
4. SearchResult[-0.55469918251038,https://stackoverflow.com/q/56757578 3, 'Composer fails with kylekatarnls/update-helper on new homestead']
5. SearchResult[-0.5561431646347,https://www.php.net/manual/en/function.oci-get-implicit-resultset.php 7, 'function oci-get-implicit-resultset']
6. SearchResult[-0.55869328975677,https://www.php.net/manual/en/function.eio-event-loop.php 1, 'function eio-event-loop']
7. SearchResult[-0.56037712097168,https://www.php.net/manual/en/function.sem-acquire.php 1, 'function sem-acquire']
8. SearchResult[-0.56422245502472,https://www.php.net/manual/en/function.imagegetclip.php 1, 'function imagegetclip']
9. SearchResult[-0.56469792127609,https://www.php.net/manual/en/function.ps-begin-pattern.php 1, 'function ps-begin-pattern']
10. SearchResult[-0.56800973415375,https://www.php.net/manual/en/book.com.php 2, 'book com']
11. SearchResult[-0.57436245679855,https://www.php.net/manual/en/function.stripos.php 3, 'function stripos']
12. SearchResult[-0.57600331306458,https://www.php.net/manual/en/function.oci-get-implicit-resultset.php 4, 'function oci-get-implicit-resultset']
13. SearchResult[-0.57834851741791,https://stackoverflow.com/q/8251426 0, 'Insert string at specified position']
14. SearchResult[-0.57876354455948,https://www.php.net/manual/en/ref.sodium.php 6, 'ref sodium']
15. SearchResult[-0.58007609844208,https://www.php.net/manual/en/cubrid.examples.php 4, 'cubrid examples']
16. SearchResult[-0.58233147859573,https://www.php.net/manual/en/function.apache-setenv.php 1, 'function apache-setenv']
17. SearchResult[-0.58316200971603,https://stackoverflow.com/q/4788500 0, 'how to monitor the CPU usage of a PHP script']
18. SearchResult[-0.58451473712921,https://www.php.net/manual/en/function.mb-ereg-match.php 1, 'function mb-ereg-match']
19. SearchResult[-0.5861394405365,https://stackoverflow.com/q/17060177 0, 'Yii2 dropdown empty option']
20. SearchResult[-0.58631610870361,https://stackoverflow.com/q/40067212 0, 'How to return custom error message from controller method validation']
21. SearchResult[-0.5878199338913,https://www.php.net/manual/en/splfileobject.ftruncate.php 1, 'splfileobject ftruncate']
22. SearchResult[-0.58884918689728,https://www.php.net/manual/en/function.checkdnsrr.php 1, 'function checkdnsrr']
23. SearchResult[-0.59443593025208,https://stackoverflow.com/q/4752389 0, 'PHP readfile() of external URL']
24. SearchResult[-0.59458327293396,https://www.php.net/manual/en/function.ibase-name-result.php 1, 'function ibase-name-result']
25. SearchResult[-0.59462285041809,https://www.php.net/manual/en/ziparchive.registerprogresscallback.php 1, 'ziparchive registerprogresscallback']
26. SearchResult[-0.59584921598434,https://www.php.net/manual/en/event.examples.php 13, 'event examples']
27. SearchResult[-0.59764385223389,https://www.php.net/manual/en/mysqli-stmt.sqlstate.php 3, 'mysqli-stmt sqlstate']
28. SearchResult[-0.59767037630081,https://www.php.net/manual/en/function.ldap-mod_del-ext.php 1, 'function ldap-mod_del-ext']
29. SearchResult[-0.59767037630081,https://www.php.net/manual/en/function.ldap-mod_replace-ext.php 1, 'function ldap-mod_replace-ext']
30. SearchResult[-0.59767037630081,https://www.php.net/manual/en/function.ldap-mod_add-ext.php 1, 'function ldap-mod_add-ext']
31. SearchResult[-0.59810423851013,https://www.php.net/manual/en/reflectionparameter.isarray.php 1, 'reflectionparameter isarray']
32. SearchResult[-0.59845781326294,https://www.php.net/manual/en/function.mb-ereg-search.php 1, 'function mb-ereg-search']
33. SearchResult[-0.60006403923035,https://www.php.net/manual/en/event.examples.php 23, 'event examples']
34. SearchResult[-0.60347318649292,https://www.php.net/manual/en/function.lchgrp.php 1, 'function lchgrp']
35. SearchResult[-0.60569077730179,https://www.php.net/manual/en/function.rpminfo.php 1, 'function rpminfo']
36. SearchResult[-0.60587275028229,https://www.php.net/manual/en/function.eio-fstatvfs.php 1, 'function eio-fstatvfs']
37. SearchResult[-0.60857331752777,https://www.php.net/manual/en/function.ps-begin-template.php 3, 'function ps-begin-template']
38. SearchResult[-0.60899668931961,https://www.php.net/manual/en/class.random-randomerror.php 1, 'class random-randomerror']
39. SearchResult[-0.60899668931961,https://www.php.net/manual/en/class.badfunctioncallexception.php 1, 'class badfunctioncallexception']
40. SearchResult[-0.60899668931961,https://www.php.net/manual/en/class.mongodb-driver-exception-unexpectedvalueexception.php 1, 'class mongodb-driver-exception-unexpectedvalueexception']
41. SearchResult[-0.60899668931961,https://www.php.net/manual/en/class.mongodb-driver-exception-invalidargumentexception.php 1, 'class mongodb-driver-exception-invalidargumentexception']
42. SearchResult[-0.60899668931961,https://www.php.net/manual/en/class.outofrangeexception.php 1, 'class outofrangeexception']
43. SearchResult[-0.60899668931961,https://www.php.net/manual/en/class.badmethodcallexception.php 1, 'class badmethodcallexception']
44. SearchResult[-0.6105665564537,https://www.php.net/manual/en/function.snmpget.php 1, 'function snmpget']
45. SearchResult[-0.61080187559128,https://stackoverflow.com/q/12496669 0, 'Code reformatting on save in PhpStorm or other jetbrains ide']
46. SearchResult[-0.61326277256012,https://www.php.net/manual/en/class.dateinterval.php 3, 'class dateinterval']
47. SearchResult[-0.61386811733246,https://www.php.net/manual/en/reflectionparameter.getclass.php 1, 'reflectionparameter getclass']
48. SearchResult[-0.6145703792572,https://www.php.net/manual/en/function.ldap-bind.php 2, 'function ldap-bind']
49. SearchResult[-0.61479258537292,https://stackoverflow.com/q/10520390 0, 'Stop script execution upon notice/warning']
```

### Reranked

Combining keyword and vector. Should be more precise.

```bash
$ composer run-script query reranked "How to stop composer giving a time-out on a script execution" 
> Composer\Config::disableProcessTimeout
> @php scripts/query.php 'reranked' 'How to stop composer giving a time-out on a script execution'
Search query: how OR to OR stop OR compos OR give OR a OR time OR out OR on OR script OR execut
0. SearchResult[-0.61479258537292,https://stackoverflow.com/q/10520390 0, 'Stop script execution upon notice/warning']
```
### HyDE

Using this functionality needs an OpenAI API key. It can be passed through a file, an environment variable or directly in the constructor. Note that calling OpenAI is _very slow_. In this example the ChatGPT text throws the vector search into some directions that are not necessarily ideal. A shorter text might had been better.

```bash
$ composer run-script query '{"class": "\\Textualization\\SemanticSearch\\VectorHydeIndex", "completion":{"open_ai_key":"/path/to/key"}}' "How to stop composer giving a time-out on a script execution" 
> Composer\Config::disableProcessTimeout
> @php scripts/query.php '{"class": "\\Textualization\\SemanticSearch\\VectorHydeIndex", "completion":{"open_ai_key":"/path/to/key"}}' 'How to stop composer giving a time-out on a script execution'
Hdydrated query: There are several ways to prevent Composer from timing out during script execution:

1. Increase the timeout limit: By default, Composer has a timeout limit of 300 seconds (5 minutes). You can increase this limit by modifying the `max_execution_time` directive in your PHP configuration file (php.ini). Set it to a higher value, such as 600 (10 minutes) or more, depending on your script's requirements.

2. Optimize your script: If your script is taking too long to execute, consider optimizing it to reduce the execution time. Analyze your code and identify any bottlenecks or areas that can be improved. Use efficient algorithms, avoid unnecessary loops or recursive calls, and optimize database queries if applicable.

3. Split your script into smaller tasks: If your script performs multiple tasks, consider breaking it down into smaller tasks and executing them separately. This way, you can avoid hitting the timeout limit by completing each task within the allowed time frame.

4. Use command-line execution: Instead of running the script through a web server, you can execute it via the command line. Command-line execution typically has a longer timeout limit compared to web server execution. You can run the script using the `php` command followed by the script's file path.

5. Increase memory limit: If your script requires more memory to execute, you can increase the memory limit in your PHP configuration file (php.ini). Modify the `memory_limit` directive and set it to a higher value, such as 256M or more, depending on your script's memory requirements.

6. Use caching: If your script performs repetitive or resource-intensive tasks, consider implementing caching mechanisms. Caching can help reduce the execution time by storing and retrieving precomputed or frequently accessed data instead of recalculating it every time.

7. Use asynchronous processing: If your script involves time-consuming operations, consider using asynchronous processing techniques. Instead of waiting for each operation to complete before moving to the next, you can execute them concurrently or in the background using tools like queues, workers, or event-driven architectures.

Remember to restart your web server or PHP service after making changes to the PHP configuration file for the changes to take effect.
0. SearchResult[-0.42923119664192,https://stackoverflow.com/q/8707339 4, 'Sharing variables between child processes in PHP?']
1. SearchResult[-0.51769161224365,https://stackoverflow.com/q/3610453 0, 'What is the best way to handle this: large download via PHP + slow connection from client = script timeout before file is completely downloaded']
2. SearchResult[-0.52345854043961,https://stackoverflow.com/q/2148114 0, 'Why use output buffering in PHP?']
3. SearchResult[-0.54594403505325,https://stackoverflow.com/q/3549243 0, 'Is it worth trying to write tests for the most tightly coupled site in the world?']
4. SearchResult[-0.58520174026489,https://stackoverflow.com/q/1190662 0, 'Cache Object in PHP without using serialize']
5. SearchResult[-0.58755302429199,https://stackoverflow.com/q/12240905 0, 'Converting a socket resource into a stream socket']
6. SearchResult[-0.60971873998642,https://stackoverflow.com/q/37502754 1, 'How to avoid repeating business logic between client and server?']
7. SearchResult[-0.6144272685051,https://stackoverflow.com/q/1883079 0, 'Best practice: Import mySQL file in PHP; split queries']
8. SearchResult[-0.61762964725494,https://stackoverflow.com/q/22845321 0, 'PHP factor 30 performance difference from Linux to Windows']
9. SearchResult[-0.62308472394943,https://stackoverflow.com/q/29975850 0, ''Delete' user but keep records (foreign keys)']
10. SearchResult[-0.6261510848999,https://stackoverflow.com/q/20637944 0, 'How to call a PHP file from HTML or Javascript']
11. SearchResult[-0.62625396251678,https://stackoverflow.com/q/30047169 0, 'phpMyAdmin Error: The mbstring extension is missing. Please check your PHP configuration']
12. SearchResult[-0.63254022598267,https://stackoverflow.com/q/24997988 0, 'Laravel or Phalcon for a heavy-traffic site']
13. SearchResult[-0.63324421644211,https://www.php.net/manual/en/pdo.prepared-statements.php 0, 'pdo prepared-statements']
14. SearchResult[-0.63616043329239,https://www.php.net/manual/en/security.general.php 0, 'security general']
15. SearchResult[-0.63763689994812,https://www.php.net/manual/en/opcache.configuration.php 14, 'opcache configuration']
16. SearchResult[-0.6404709815979,https://stackoverflow.com/q/8607308 0, 'Testing Legacy PHP Spaghetti Code?']
17. SearchResult[-0.64080655574799,https://stackoverflow.com/q/23595036 0, 'MVC (Laravel) where to add logic']
18. SearchResult[-0.64219701290131,https://stackoverflow.com/q/1642517 0, 'Combined HTML, PHP and Javascript indenting and syntax highlighting in vim']
19. SearchResult[-0.64234572649002,https://stackoverflow.com/q/10992857 0, 'Composition vs Inheritance. What should I use for my database interaction library?']
20. SearchResult[-0.64483368396759,https://stackoverflow.com/q/12233406 1, 'Preventing session hijacking']
21. SearchResult[-0.6483439207077,https://stackoverflow.com/q/11930208 0, 'How to keep extending session life when user is active?']
22. SearchResult[-0.64999687671661,https://stackoverflow.com/q/10252476 1, 'Apache Won't Request My SSL Client Certificate']
23. SearchResult[-0.65274918079376,https://stackoverflow.com/q/8866738 0, 'Speed of PHP vs JavaScript?']
24. SearchResult[-0.65286993980408,https://stackoverflow.com/q/16603365 6, 'PDO DELETE unexpectedly slow when working with millions of rows']
25. SearchResult[-0.65296018123627,https://stackoverflow.com/q/540339 0, 'How to check if directory contents has changed with PHP?']
26. SearchResult[-0.6578665971756,https://stackoverflow.com/q/16826582 0, 'How do I architect my classes for easier unit testing?']
27. SearchResult[-0.66181313991547,https://stackoverflow.com/q/8707339 5, 'Sharing variables between child processes in PHP?']
28. SearchResult[-0.66558104753494,https://stackoverflow.com/q/10385582 0, 'session_start seems to be very slow (but only sometimes)']
29. SearchResult[-0.66711056232452,https://stackoverflow.com/q/349924 0, 'Is the LAMP stack appropriate for Enterprise use?']
30. SearchResult[-0.66831797361374,https://stackoverflow.com/q/975929 0, 'Firefox error 'no element found'']
31. SearchResult[-0.67009365558624,https://stackoverflow.com/q/19249159 1, 'Best practice multi language website']
32. SearchResult[-0.67072737216949,https://stackoverflow.com/q/15967157 0, 'MATLAB executable is too slow']
33. SearchResult[-0.67285722494125,https://stackoverflow.com/q/30235551 0, 'Laravel query builder - re-use query with amended where statement']
34. SearchResult[-0.67627030611038,https://stackoverflow.com/q/10829566 0, 'Phing and Composer, which way around?']
35. SearchResult[-0.67773687839508,https://stackoverflow.com/q/62043037 2, 'Elasticsearch - Want to sort by field in all indices where that particular field available or not if not then avoid it']
36. SearchResult[-0.67950439453125,https://stackoverflow.com/q/15473019 0, 'Vulnerabilities of PHP's (deprecated) mysql module vs. MySQLi & PDOs']
37. SearchResult[-0.6814968585968,https://www.php.net/manual/en/features.gc.refcounting-basics.php 6, 'features gc refcounting-basics']
38. SearchResult[-0.68179941177368,https://stackoverflow.com/q/2077576 2, 'PHP & mySQL: When exactly to use htmlentities?']
39. SearchResult[-0.68195909261703,https://www.php.net/manual/en/pdo.transactions.php 0, 'pdo transactions']
40. SearchResult[-0.68209862709045,https://stackoverflow.com/q/48927866 19, 'How to use phpseclib to verify that a certificate is signed by a public CA?']
41. SearchResult[-0.68375432491302,https://stackoverflow.com/q/12102195 0, 'How to query sql with active record for dates between specified times']
42. SearchResult[-0.68424761295319,https://stackoverflow.com/q/1746207 0, 'How to IPC between PHP clients and a C Daemon Server?']
43. SearchResult[-0.68476867675781,https://stackoverflow.com/q/1088987 0, 'AJAXify site']
44. SearchResult[-0.68500161170959,https://www.php.net/manual/en/security.errors.php 2, 'security errors']
45. SearchResult[-0.69128942489624,https://stackoverflow.com/q/1849803 0, 'Are Prepared Statements a waste for normal queries? (PHP)']
46. SearchResult[-0.69598364830017,https://www.php.net/manual/en/philosophy.parallel.php 0, 'philosophy parallel']
47. SearchResult[-0.6972119808197,https://stackoverflow.com/q/12060865 0, 'openssl_pkey_new() throwing errors -- Proper openssl.cnf setup for php']
48. SearchResult[-0.69859385490417,https://stackoverflow.com/q/2022248 0, 'PHP Web Application: mysql database design best practices question']
49. SearchResult[-0.69992303848267,https://stackoverflow.com/q/19249159 0, 'Best practice multi language website']
```

### Fetching documents

Given a URL and chunk number, the fetch script does the trick:

```bash
$ composer run-script fetch vector https://stackoverflow.com/q/2077576 2
> @php scripts/fetch.php 'vector' 'https://stackoverflow.com/q/2077576' '2'
URL: https://stackoverflow.com/q/2077576
Chunk: 2
Title: PHP & mySQL: When exactly to use htmlentities?
Offset-start: 2169
Offset-end: 3238
Section: StackOverlow
License: CC-BY-SA-3.0
Text:
 <p>I thought it might help to add some more specific details of a specific situation here. Consider that there is a 'Preview' page. Now when I submit the input from a textarea, the Preview page receives the input and shows it html and at the same time, a hidden input collects this input. When the submit button on the Preview button is hit, then the data from the hidden input is POST'ed to a new page and that page inserts the data contained in the hidden input, into the DB. If I do not apply htmlentities when the form is initially submitted (but apply only strip_tags and mysql_real_escape_string) and there's a malicious input in the textarea, the hidden input is broken and the last few characters of the hidden input visibly seen as  <code>" /&gt;</code> on the page, which is undesirable. So keeping this in mind, I need to do something to preserve the integrity of the hidden input properly on the Preview page and yet collect the data in the hidden input so that it does not break it. How do I go about this? Apologize for the delay in posting this info.</p>
```

Note that the keyword index is unchunked (all chunk numbers are 0).



## Indexing files

The documents are provided as a `JSONL` file in the format described at the top of this document.

A sample file is available for [for download](http://textualization.com/download/phpsemsearch_0.1.tar.bz2).

```bash
$ composer run-script index keyword sophp.jsonl
```

```bash
$ composer run-script index vector sophp.jsonl
```

To use a reranked index, create a vector and keyword indexes separately.

The vector indexing takes about a day and consumes significant amount of RAM at the moment.

## Chunking files

The recursive chunker can be used standalone:

```bash
$ composer run-script chunk ropherta null 1024 tests/sornd1000.jsonl output.jsonl
```

The output `JSONL` documents have keys:

* Title (`title`)
* Text (`text`)
* URL (`url`)
* Chunk Number (`chunk_num`)
* Offset Start (`offset_start`)
* Offset End (`offset_end`)
* License (`license`)

Other tokenizers are possible, see the code in the `scripts` folder. Using the string `null` (n-u-l-l) sets the size to characters instead of tokens.

## Computing embeddings with custom ONNX model

```bash
$ composer run-script embed /path/to/model.onnx "actual text to compute embeddings"
```

## HyDE-rating files

To expand answers using a completion service (like Open AI ChatGPT) use:

```bash
$ composer run-script hydrate /path/to/open-ai-key 1024 tests/sornd1000.jsonl output.jsonl
```

It populates the field `completion` from the `title` in the `JSON` object.



## Sponsors

We thank our sponsor:

<a href="https://evoludata.com/"><img src="https://evoludata.com/display208"></a>
