from asyncio.windows_events import NULL
import cv2
import qrcode
from cryptography.fernet import Fernet
import mysql.connector as mariadb
import time

eleves = {  "1111": "Killian",
            "1112": "Kevin",
            "1113": "Muharem",
            "1114": "David"
            }

  
d = cv2.QRCodeDetector()

def QrCreator(id , name) :
                                                            ## Fonction pour créer un Qrcode par eleves avec un numéro de série
    img = qrcode.make(id)
    img.save(name+"QR.png")
    fileName = name+"QR.png"



def QrReader(name):                                          ## Fonction de lecture de Qrcode et reconaissance d'éleves

    val, points, QRcode = d.detectAndDecode(cv2.imread(name+"QR.png"))   

    
    if( DB_Get_Qrid( val) != None):
         print( "Bonjour "+DB_Get_Name(val) + " !")
    else :
         print("éleve non reconu") 


def VideoReader():                                      ##Fonction de lecture de QRcode par caméra

    cap = cv2.VideoCapture(0)
    while True :
        _,img = cap.read()
        val,points,QRcode = d.detectAndDecode(img)
        cv2.imshow('qrcode scaner app',img)
        if val :
           
            if( eleves.get(val) != None):
                print( "Bonjour "+eleves[val] + " !")
                break
            else : 
                print("éleve non reconu") 
            break
        if cv2.waitKey(1)==ord('q'):
            break
    cv2.destroyAllWindows()

def Crypter(object):                            ##Fonction d'encryptage et de decryptage


    # key = Fernet.generate_key()

    crypter = Fernet(b'7VGDN8Ly_Qaknwqd4FNAl_r3CuF4rRSfM9ezCR49GWo=')

    Pass = crypter.encrypt( bytes(str(object), encoding = "utf-8"))
    Pass = Pass.decode()
    return(Pass)


def Decrypter(object):

    crypter = Fernet(b'7VGDN8Ly_Qaknwqd4FNAl_r3CuF4rRSfM9ezCR49GWo=')
    Decrypted = crypter.decrypt(object)
    Decrypted = Decrypted.decode()
    print(Decrypted)
    return(Decrypted)
  

def DB():
    
    mariadb_connection = mariadb.connect(host = 'pli-db00008999.mdb0002865.db.skysql.net',database = 'MainDB', user = 'DB00008999', password = 'kidake2023PLI!', ssl_ca ='skysql_chain.pem',port = 5008)
    create_cursor = mariadb_connection.cursor()


def DB_Get_Qrid(qrid):
    mariadb_connection = mariadb.connect(host = 'pli-db00008999.mdb0002865.db.skysql.net',database = 'MainDB', user = 'DB00008999', password = 'kidake2023PLI!', ssl_ca ='skysql_chain.pem',port = 5008)
    create_cursor = mariadb_connection.cursor()

    create_cursor.execute(f"SELECT matricule FROM student WHERE matricule = {qrid} ")
    myresult = create_cursor.fetchall()
    create_cursor.close() 

    if (len(myresult) == 0):
        return(None) 
    else:      
        return(myresult[0][0]) 

def DB_Get_Name(qrid):
    mariadb_connection = mariadb.connect(host = 'pli-db00008999.mdb0002865.db.skysql.net',database = 'MainDB', user = 'DB00008999', password = 'kidake2023PLI!', ssl_ca ='skysql_chain.pem',port = 5008)
    create_cursor = mariadb_connection.cursor()

    create_cursor.execute(f"SELECT last_name FROM student WHERE matricule = {qrid} ")
    myresult = create_cursor.fetchall() 
    create_cursor.close()

    if (len(myresult) == 0):
        return(None) 
    else:
        return(myresult[0][0])


def DB_Present(qrid):
    mariadb_connection = mariadb.connect(host = 'pli-db00008999.mdb0002865.db.skysql.net',database = 'MainDB', user = 'DB00008999', password = 'kidake2023PLI!', ssl_ca ='skysql_chain.pem',port = 5008)
    create_cursor = mariadb_connection.cursor()

    create_cursor.execute(f"SELECT presence FROM student WHERE matricule = {qrid} ")
    myresult = create_cursor.fetchall() 

    if(myresult[0][0] == 0):
        create_cursor.execute(f"UPDATE student SET presence = 1 WHERE matricule = {qrid} ")
        mariadb_connection.commit()
        print("present")
    else:
        create_cursor.execute(f"UPDATE student SET presence = 0 WHERE matricule = {qrid} ")
        mariadb_connection.commit()
        print("reset")
    
    create_cursor.close()


def Presential_Reader():
    cap = cv2.VideoCapture(0)
    while True :
        _,img = cap.read()
        val,points,QRcode = d.detectAndDecode(img)
        cv2.imshow('qrcode scaner app',img)
        if val :
           
            if(DB_Get_Name(val) != None):
                DB_Present(val)
                time.sleep(2)
                
                # break
            else :
                print("éleve non reconu") 
                time.sleep(2)

            # break
        if cv2.waitKey(1)==ord('q'):
            break
    cv2.destroyAllWindows()





# DB_Get_Qrid(829)
# DB_Get_Name(166)
# DB()      
# QrCreator(829,"829")
#QrReader("4")
#VideoReader()
#Crypter("1111")
# Decrypter(Crypter("1111"))
# DB_Present(166)
#print (DB_Present(1111))
Presential_Reader()
