import urllib
import urllib2
import json
import copy
import httplib
import socket
import sys
import time
import requests
import os
import pandas as pd

from linebot import LineBotApi
from linebot.models import TextSendMessage
from linebot.exceptions import LineBotApiError

DEBUG = True

accessToken = "+eU+zQe8QJL9BraZ55TJLLTtUNQ1jDojYN63o5t3Skx2cnTqmXrr5lJNXUNBGVM8mSCtidORd7MgL6neDJf5uI5gKWhR3eUiKuqGNCdh/1ptR4Fdig9RCNHJo9tZUNJjjhH3N+MAtzE3+YVeAjlRIgdB04t89/1O/w1cDnyilFU="
    
api_key="4csW3sDVAQwWESHj37IW_1XkRSAvhVwA"

base_url = "https://api.mlab.com/api/1/databases/tstdb/collections/linebot?apiKey="

curr_url = "https://api.mlab.com/api/1/databases/tstdb/collections/currentValue?apiKey="

line_bot_api = LineBotApi(accessToken)

httplib.debuglevel = 1
httplib.HTTPConnection.debuglevel = 1

#TCP_IP = '127.0.0.1'
TCP_IP = '192.168.0.5'
TCP_PORT = 1025
BUFFER_SIZE = 1024
nDat = 0
data = ''
q = 0

def getdata_api(url_api):
    response = urllib2.urlopen(url_api +str(api_key)).read()
    data = json.loads(response)
    nDat = len(data)
    return data, nDat

def getIndex(dat, q):
    dframe = pd.DataFrame(dat)
    wendy = dframe.loc[dframe['No'] == q]
    return wendy

def find_index(dataName, num):
    return dataName[num]['Name'].find('\'');

def new_name(dataName, ind, num):
    return dataName[num]['Name'][:ind] + '\\' + dataName[num]['Name'][ind:]

def set_data(dataName , new_name, num):
    return ("insert into coffDB (No, Name, Coffee, URL) values ('%s', '%s', '%s', '%s')" % (dataName[num]['No'], new_name, dataName[num]['Coffee'], dataName[num]['PicProfile']))


def is_ascii(s):
    return all(ord(c) < 128 for c in s)

def main():
    oDat = 0
    nDat = 0
    cnt = 0
    queue = 0
    Debug = True
    
    print '******:= Start =:******'

    s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)

    while True:
        try:
            s.connect((TCP_IP, TCP_PORT))         # "random" IP address and port
        except socket.error, exc:
            print "Caught exception socket.error : %s" % exc
        else: 
            break
    try:
        buff, queue = getdata_api(curr_url)
    except urllib2.URLError:
        print "urllib2.URLError"


    print 'Sending to start'

    while Debug:
         
        while True:
            try:
                s.send("Hello Yumi")
            except socket.error, exc:
                print "Caught exception socket.error : %s" % exc
                s.connect((TCP_IP, TCP_PORT))
            else: 
                break

        try:
            if s.recv(BUFFER_SIZE) == "Hello Python":
                print 'Data Recieved'  
                s.send("ack")
                
                Debug = False
                break
        except socket.timeout:
            print("time out")

    s.settimeout(3)

    
    try:
        data, nDat = getdata_api(base_url)
    except urllib2.URLError:
        print "urllib2.URLError"

    while True:
        try:             
            recv_data = s.recv(BUFFER_SIZE)  
            print recv_data
            if recv_data == "order":
                #os.startfile('welcome.mp3')
                if queue <= len(data) - 1:
                    queue += 1
                    while True:
                        ind = getIndex(data, queue)
                        if len(ind) != 0:
                            break
                        else:
                            queue += 1
                    while True:
                        try:
                            s.send(data[ind['No'].index[0]]['Coffee'])
                        except socket.error, exc:
                            print "Caught exception socket.error : %s" % exc
                            s.connect((TCP_IP, TCP_PORT))
                        else: 
                            print "Order sended"
                            url = 'https://translate.google.com/translate_tts?ie=UTF-8&tl=en-US&client=tw-ob&q= Khun. '+data[ind['No'].index[0]]['Name']+'\'s. '+data[ind['No'].index[0]]['Coffee'].lower()+' ready to serve. Please enjoy your drink' 
                    
                            r = requests.get(url, stream=True)
                            with open("file.mp3", 'wb') as f:
                                for chunk in r.iter_content(chunk_size=1024): 
                                    if chunk: # filter out keep-alive new chunks
                                        f.write(chunk)
                            break
                else:
                    
                    while True:
                        try:
                            s.send(" ")
                        except socket.error, exc:
                            print "Caught exception socket.error : %s" % exc
                            s.connect((TCP_IP, TCP_PORT))
                        else: 
                            break    

            elif recv_data == "finish":
                data_pos = {'currentValue' : 'x'}
                headers = {'content-type': 'application/json'}
                r = requests.post(curr_url+api_key, data=json.dumps(data_pos), headers=headers)
                os.startfile('file.mp3')

                try:
                    line_bot_api.push_message(data[ind['No'].index[0]]['UserId'], TextSendMessage(text='Coffee ready to serve. :)'))
                except LineBotApiError as e:
                    print "error handle"
                
                while True:
                    try:
                        s.send("ack finish")
                    except socket.error, exc:
                        print "Caught exception socket.error : %s" % exc
                        s.connect((TCP_IP, TCP_PORT))
                    else: 
                        break
                
                print "Complete order"

            else:
                while True:
                    try:
                        s.send("err")
                    except socket.error, exc:
                        print "Caught exception socket.error : %s" % exc
                        s.connect((TCP_IP, TCP_PORT))
                    else: 
                        break
                
        except socket.timeout:
            print("time out")

        except socket.error:
            print "socket err"
            s.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
            print s

        if(oDat < nDat):
            oDat = nDat  
                
        else:
            try:
                data, nDat = getdata_api(base_url)
            except urllib2.URLError:
                print "urllib2.URLError"

            if cnt < nDat:
               # print show_data(data, cnt)
                cnt=cnt+1

main()
