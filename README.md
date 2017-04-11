# Chromatic Encryption

This code is very similar to the one of the original v.1.3 in varocarbas.com (http://varocarbas.com/tools/chromatic/1.3/). I have only 
removed/adapted parts dealing with private or irrelevant information. The functionalities (encryption/decryption) are identical. 

This is a ready-to-use application which only needs the following basic modifications:
- Updating the DB-connection information in the db.php file.
- Creating the basic0 table in the DB by using basic0_dump.sql.
- Updating the values of the URL/path variables at the top of the index.php file by emulating the provided (original) examples.
- The original varocarbas.com code deals with .htaccess-modified URLs. The attached sample file contains the only RewriteRule which is required to execute this code.

For the time being, I will not explain the algorithm or include descriptive comments.

--- 

# AUTHORSHIP & COPYRIGHT
I, Alvaro Carballo Garcia (= varocarbas), am the sole author of each single bit of this code.

Equivalently to what happens with all my other online contributions, this code can be considered public domain. For more information about my copyright/authorship attribution ideas, visit the corresponding pages of my sites:

https://customsolvers.com/en/pages/company/legal/copyright/
ES: https://customsolvers.com/es/paginas/empresa/legal/copyright/
https://varocarbas.com/copyright/
ES: https://varocarbas.com/copyright_es/
