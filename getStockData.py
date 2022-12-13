# -*- coding: utf-8 -*-
"""
Created on Sat Feb 13 22:09:54 2020

@author: Pat McGee

This script reads all of yesterdays stock information and stores it in a database

"""

class stock(object):
     pass

import requests
from bs4 import BeautifulSoup
import re
import mysql.connector
from datetime import datetime
import json
import time

dateToScrape = "2021-02-12"
dateToScrape = time.strptime(dateToScrape, "%Y-%m-%d")
errorObjects = []

# read file
with open('data.json', 'r') as myfile:
    data=myfile.read()

# parse file
obj = json.loads(data)


def connectToDb():
     print("Starting Scraper at ", datetime.now())
     
     try:
           cnx = mysql.connector.connect(user='root', password='',
                                   host='127.0.0.1',
                                   database='stockninja')
           print("Connected to MySQL Database") 
           return True
      
     except:
           print("Can't connect to SQL Server...")
           return False

if (connectToDb()):
     
     cnx = mysql.connector.connect(user='root', password='',
                                   host='127.0.0.1',
                                   database='stockninja')
               
     for properties in obj:
          symbol = str(properties["Symbol"])
          
          
          
          mycursor = cnx.cursor(buffered=True)
     
          # Create the time series data table
          mycursor.execute("SHOW TABLES LIKE '%Data%'; ")
          myresult = mycursor.fetchall()
          
          if(not myresult):
               print("no data table found.... creating")
               mycursor.execute("CREATE TABLE `stockninja`.`data` (`Id` INT UNSIGNED NOT NULL AUTO_INCREMENT,PRIMARY KEY (`Id`));")

          
          sql = "SHOW COLUMNS FROM `data` LIKE 'Symbol';"
          mycursor.execute(sql)
          myresult = mycursor.fetchall()
          if (not myresult):
               print("Symbol column not found, creating...")
               sql = "ALTER TABLE `stockninja`.`data` ADD COLUMN Symbol VARCHAR(20);"
               mycursor.execute(sql)


          sql = "SHOW COLUMNS FROM `data` LIKE 'Data';"
          mycursor.execute(sql)
          myresult = mycursor.fetchall()
          if (not myresult):
               print("Data column not found, creating...")
               sql = "ALTER TABLE `stockninja`.`data` ADD COLUMN Data VARCHAR(65000);"
               mycursor.execute(sql)
          
          # Create the RSI Table
          mycursor.execute("SHOW TABLES LIKE '%rsi%'; ")
          myresult = mycursor.fetchall()
          
          if(not myresult):
               print("no RSI table found.... creating")
               mycursor.execute("CREATE TABLE `stockninja`.`rsi` (`Id` INT UNSIGNED NOT NULL AUTO_INCREMENT,PRIMARY KEY (`Id`));")

          
          sql = "SHOW COLUMNS FROM `rsi` LIKE 'Symbol';"
          mycursor.execute(sql)
          myresult = mycursor.fetchall()
          if (not myresult):
               print("Symbol column not found, creating...")
               sql = "ALTER TABLE `stockninja`.`rsi` ADD COLUMN Symbol VARCHAR(20);"
               mycursor.execute(sql)


          sql = "SHOW COLUMNS FROM `rsi` LIKE 'Data';"
          mycursor.execute(sql)
          myresult = mycursor.fetchall()
          if (not myresult):
               print("RSI.Data column not found, creating...")
               sql = "ALTER TABLE `stockninja`.`rsi` ADD COLUMN Data VARCHAR(65000);"
               mycursor.execute(sql)
          
          
          url = "https://www.alphavantage.co/query?function=TIME_SERIES_INTRADAY&symbol="+symbol+"&interval=1min&outputsize=full&apikey=79H81T3WZM0Q6NF4"
          #url = "https://www.alphavantage.co/query?function=RSI&symbol="+symbol+"&interval=1min&time_period=10&series_type=open&apikey=79H81T3WZM0Q6NF4"
          page = requests.get(url)
          currentStock = json.loads(page.text)
          
          #-------------------------------------------------------------------
          #     Capture the RSI, but only pertaining to that day
          #-------------------------------------------------------------------
          # tempStockObject = stock()
          
          # # print(currentStock)
          # output = '{'
          # counter = 0
          # try:
          #      for stockDate in currentStock["Technical Analysis: RSI"]:
               
          #           if (time.strptime(stockDate[0:10], "%Y-%m-%d") == dateToScrape):
          #                #print(stockDate)
          #                if (counter != 0):
          #                     output = output + ","
                              
          #                output = output + '"'+stockDate+'": {'
     
          #                tempStockTimeObject = stock()
                         
          #                counter2 = 0
          #                for stockDateTime in currentStock["Technical Analysis: RSI"][stockDate]:
          #                      #print(stockDateTime)
          #                      output = output + '"RSI" : "'+currentStock["Technical Analysis: RSI"][stockDate][stockDateTime]+'" }'
          #                      #print(currentStock["Technical Analysis: RSI"][stockDate][stockDateTime])
                                    
          #                      setattr(tempStockTimeObject, stockDateTime, currentStock["Technical Analysis: RSI"][stockDate][stockDateTime])
                         
          #           setattr(tempStockObject, stockDate, tempStockTimeObject)
          #           counter = counter + 1
          # except:
          #      pass
          
          # output = output + "}"
          
          # sql = "SELECT * FROM rsi WHERE Symbol = '"+symbol+"'"
          # mycursor.execute(sql)
          # myresult = mycursor.fetchall()
          # if (not myresult):
          #       print("no RSI entry for " + symbol + ", adding...")
          #       sql = "INSERT INTO rsi (Symbol, Data) VALUES (%s,%s)"
          #       try:
          #           mycursor.execute(sql, (symbol, output))
          #       except:
          #           print("data error!")
          #           mycursor.execute(sql, (symbol, "No data"))
          #       print("added.")
          # else:
          #       print("Data already exists for "+symbol+", skipping")
                
          
          
          #-------------------------------------------------------------------
          #     Capture the Trading Data, but only pertaining to that day
          #-------------------------------------------------------------------
          tempStockObject = stock()
          
          # print(currentStock)
          output = '{'
          counter = 0
          
          try:
               for stockDate in currentStock["Time Series (1min)"]:
                    
                    if (counter % 2 == 0):
                         counter2 = 0
                         if (time.strptime(stockDate[0:10], "%Y-%m-%d") == dateToScrape):
                              #print(stockDate)
                              if (counter != 0):
                                   output = output + ","
                                   
                              output = output + '"'+stockDate+'": {'
                              
                              counter2 = 0
                              for stockAttribute in currentStock["Time Series (1min)"][stockDate]:
                                   if (counter2 != 0):
                                        output = output + ","
                                    #print(stockDateTime)
                                   output = output + '"'+stockAttribute+'" : "'+currentStock["Time Series (1min)"][stockDate][stockAttribute]+'"'
                                   #print(currentStock["Technical Analysis: RSI"][stockDate][stockDateTime])
                                   counter2 = counter2 + 1
                                    
                              output = output + "}"
                         
                    counter = counter + 1
          except:
               pass
          
          # output = output + "}"
          # if (len(output) > 65000):
          #       print(symbol + " exceeded 65000 characters " + str(len(output)))
          #       errorObjects.append(symbol)
          # else:
          #      print(symbol + " will be added successfully ("+str(len(output))+")")
          
          sql = "SELECT * FROM data WHERE Symbol = '"+symbol+"'"
          mycursor.execute(sql)
          myresult = mycursor.fetchall()
          if (not myresult):
                print("no Data entry for " + symbol + ", adding...")
                sql = "INSERT INTO data (Symbol, Data) VALUES (%s,%s)"
                try:
                    mycursor.execute(sql, (symbol, output))
                except:
                    print("data error!")
                    mycursor.execute(sql, (symbol, "No data"))
                print("added.")
          else:
                print("Data already exists for "+symbol+", updating")
                sql = 'UPDATE data SET data = \''+output+'\' WHERE Symbol = \''+symbol+'\''
                try:
                    mycursor.execute(sql)
                except:
                    print("error updating data!")
                print("updated.")
                

          # sql = "SELECT * FROM data WHERE Symbol = '"+symbol+"'"
          # mycursor.execute(sql)
          # myresult = mycursor.fetchall()
          # if (not myresult):
          #       print("no entry for " + symbol + ", adding...")
          #       sql = "INSERT INTO data (Symbol, Data) VALUES (%s,%s)"
          #       try:
          #           mycursor.execute(sql, (symbol, str(currentStock["Time Series (1min)"])))
          #       except:
          #           print("data error!")
          #           mycursor.execute(sql, (symbol, "No data"))
          #       print("added.")
          # else:
          #       print("Data already exists for "+symbol+", skipping")

          
print("done")
#print("Number of stocks that exceeded 65000 characters: " + str(len(errorObjects)))
