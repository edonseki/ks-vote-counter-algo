# ks-vote-counter-algo

This repository contains the algorithm of being able to detect the filled physical ballot paper and extracting the parties and candidates voted. Currently it has some limitations regarding the original ballot paper indexing size and the one filled from the people.

The part of code only extracts specific data. In order to have a full automatic process there is more technical and procedural handling nedded. 

### How it works?

1. First the algorithm needs to index an ballot paper that has marked the fillable field by two specific colors:
    - RGB: 23,38,118 - the color that full fills the boxes for parties
    - RGB: 129,20,20 - the color that full fills the boxes for candidates
    
    Finally the ballot paper with indexes marked should look like the image below:
    ![Alt text](images/ballotpaper-toindex.png?raw=true "The image to index")

   To index the file you have to use `src/ballotpaper-index.php`
   
   The command: `php src/ballotpaper-index.php {the image to be indexed} {an empty ballot paper} {the path where the indexed file will be stored}`
   Example: From terminal call this command: `php src/ballotpaper-index.php images/ballotpaper-toindex.png images/ballotpaper-empty.png indexes/`
   
   
 2. After step 1 the command will create a TXT file that will contain necessary data for detecting the user manual input.
 
 3. In order to extract data from a manual filled ballotpaper you have to use the `src/ballotpaper-scan.php` 
 
    The command: `php src/ballotpaper-scan.php {the index file path} {the png file of ballot paper}`
    From terminal call this command: `php src/ballotpaper-scan.php indexes/ballotpaper_index.txt images/ballotpaper-voted1.png`
    
    Input:
    
    
    ![Alt text](images/ballotpaper-voted1.png?raw=true "An example")

    Output:
    
    And the out put would be something like: `{"parties":[11],"candidates":[6,11,18,27,31]}`
    The parties entity includes only the position from top to bottom and not the real reference number. The candidates number contain the real number of the box.
    
    
  
