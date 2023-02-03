# from asyncio.windows_events import NULL
import cv2
import qrcode
from cryptography.fernet import Fernet
import mysql.connector as mariadb

eleves = {  "1111": "Killian",
            "1112": "Kevin",
            "1113": "Muharem",
            "1114": "David"
            }

  
d = cv2.QRCodeDetector()

def QrCreator(id , name) :
    
    #Name = input("Name of the student : ")
    #Nb = input("Enter Serial Number :")         ## Fonction pour créer un Qrcode par eleves avec un numéro de série
    img = qrcode.make(id)
    img.save(name+"QR.png")
    fileName = name+"QR.png"



def QrReader(name):                                          ## Fonction de lecture de Qrcode et reconaissance d'éleves
    
    #Name = input("Name of the student : ")
    val, points, QRcode = d.detectAndDecode(cv2.imread(name+"QR.png"))   

    
    if( DB_Get_Qrid( val) != None):
         print( "Bonjour "+DB_Get_Name(val) + " !")
    else :
         print("éleve non reconu") 
   

    
    # if( eleves.get(val) != None):
    #     print( "Bonjour "+eleves[val] + " !")
    # else :
    #     print("éleve non reconu") 

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
    # Descrypting = crypter.decrypt(Pass)

    # print("ID encoder :" + "\n" + str(Pass))
    # print("\n")
    # print("ID décoder :" + "\n" + str(Descrypting))

def Decrypter(object):

    crypter = Fernet(b'7VGDN8Ly_Qaknwqd4FNAl_r3CuF4rRSfM9ezCR49GWo=')
    Decrypted = crypter.decrypt(object)
    Decrypted = Decrypted.decode()
    print(Decrypted)
    return(Decrypted)
  

def DB():
    
    mariadb_connection = mariadb.connect(host = 'localhost',database = 'test_pli', user = 'root', password = 'root')
    create_cursor = mariadb_connection.cursor()

    # create_cursor.execute(f"SELECT qrid FROM users WHERE qrid = {qrid} ")
    # myresult = create_cursor.fetchall()       
    # print(myresult[0][0])
     
    # create_cursor.execute('SELECT * from users')
    # myresult = create_cursor.fetchall()       #Afficher les data d'une table
    # return(myresult)   
    
    ##create_cursor.execute("SHOW TABLES")  afficher les tables
    ##for x in create_cursor:
    ##    print(x)


def DB_Get_Qrid(qrid):
    mariadb_connection = mariadb.connect(host = 'localhost',database = 'test_pli', user = 'root', password = 'root')
    create_cursor = mariadb_connection.cursor()

    create_cursor.execute(f"SELECT qrid FROM users WHERE qrid = {qrid} ")
    myresult = create_cursor.fetchall()
    create_cursor.close() 

    if (len(myresult) == 0):
        return(None) 
    else:      
       return(myresult[0][0]) 

def DB_Get_Name(qrid):
    mariadb_connection = mariadb.connect(host = '127.0.0.1',database = 'scrutiny0', user = 'david', password = 'bob')
    create_cursor = mariadb_connection.cursor()

    create_cursor.execute(f"SELECT first_name FROM student WHERE qrid = {qrid} ")
    myresult = create_cursor.fetchall() 
    create_cursor.close()

    if (len(myresult) == 0):
        return(None) 
    else:
        return(myresult[0][0])


def DB_Present(qrid):
    mariadb_connection = mariadb.connect(host = '127.0.0.1',database = 'scrutiny0', user = 'david', password = 'bob')
    create_cursor = mariadb_connection.cursor()

    create_cursor.execute(f"UPDATE users SET presence = 1 WHERE qrid = {qrid} ")
    mariadb_connection.commit()
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
                break
            else :
                print("éleve non reconu") 
            break
        if cv2.waitKey(1)==ord('q'):
            break
    cv2.destroyAllWindows()




# DB_Get_Qrid(1114)
#DB()      
#QrCreator(")
#QrReader("4")
#VideoReader()
#Crypter("1111")
# Decrypter(Crypter("1111"))
#DB_Present(1112)
#print (DB_Present(1111))
# pip install mysql-connector-python==8.0.28
Presential_Reader()
