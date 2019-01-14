## Trip Api

A REST API to get activities planned per day. The Api does not use any PHP framework.

## Pre-requisities
1. PHP >= 7.1.0
2. Install composer https://getcomposer.org/


## How to setup?
1. Install php on your machine
 
   PHP installations on different platforms please refer to `http://php.net/manual/en/install.php`
   
2. Install composer
   For Linux/Mac, the below script will simply check some php.ini settings, warn you if they are set incorrectly, and then download the
   latest composer.phar in the current directory. The 4 lines above will, in order:

   - Download the installer to the current directory
   - Verify the installer SHA-384 which you can also cross-check here
   - Run the installer
   - Remove the installer
   ```
   php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
   php -r "if (hash_file('sha384', 'composer-setup.php') === '93b54496392c062774670ac18b134c3b3a95e5a5e5c8f1a9f115f203b75bf9a129d5daa8ba6a13e2cc8a1da0806388a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
   php composer-setup.php --install-dir=bin --filename=composer
    ```
    
   For further information and other OS refer `https://getcomposer.org/`
     
4. Install dependencies (important !)
     
     `composer update`
          
5. Load class map
   
   `composer dumpautoload -o`
         
6. Start PHP built-in web server :http://php.net/manual/en/features.commandline.webserver.php
       
       php -S localhost:8000 
  
## Invoke api

   The planner api endpoint can be invoked as -

   ```
   curl -A GET 'http://localhost:8000/App/Modules/Activities/Select.php?budget=500&days=3'
   ```

## Assumptions/Considerations

1. The currency of all activities and input budget is assumed to be same

2. The default city is Berlin and default country of search is Germany. There params could be used
   in future for other cities.
   
3. Greedy algorithm is used to populate the activities. The algorithm is greedy on the number of activities that can
   be performed each day. Since the algorithm is greedy, it does not go back and reverse the decision to add
   another package combination to obtain the optimal solution.     

4. If Api fails to meet any of the constraints mentioned in requirements, a 400 status is returned -

   - Minimum activities output per day is 3
   - Input Budget should be between 100 and 5000 inclusive
   - Budget per day should be at least 50
   - Total input days should be between 1 to 5 inclusive
   - There should be 30 min commute time between activities
   - All activities each day start from 10:00 at intervals of 30 mins
   - Last activity each day has to be completed before 22:00 hrs   

5. The time is relocation is strictly the commute time between 2 activities which is given as 30 minutes. The first 
   activity everyday has no commute time.
   
   If previous activity ends at 10:25, the time to commute to next activity is 30 mins i.e 10.55.
   However since it is mentioned that -  `Activities start every 30 mins after 10:00`,
   Next activity will only start at 11:00 as per above rule. But the 5 extra mins are not counted in total relocation time
   calculation.
   
6. The database of activities is sent by product team and can be inserted in mongodb or any other database and queried
   to get the activities  chosen by city and country & sorted by price.
   
## Ideal solution

   - The current implementation is greedy on the number of activities performed each day. It sorts the activities in 
     ascending order of price which naturally gives us shorter duration activities first ( less duration => less price) 
     and hence maximum activities can be accommodated in 1 day.
       
     However the drawback is, if large budget is given in input, only small amount of it gets used. This might be not
     suitable for the business. To draw money from the customers, the activity provider API should ideally give a mix
     of low cost, mid cost and expensive activities per day. One way to achieve this is to divide the data set in 3 ranges:
       - low
       - mid
       - high
       
     Iterate over the 3 ranges to pick mix of activities from each until time runs out each day.
   
   - In current implementation, if many short duration activities are picked up, the total relocation time increases.
     Ideally customers would want few longer duration tasks to reduce the relocation time. 
   