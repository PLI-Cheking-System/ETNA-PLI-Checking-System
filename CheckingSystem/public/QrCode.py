import qrcode
import sys

def QrCreator(matricule,firstname,lastname):
    ##Name = input("Name of the student : ")
    ##Nb = input("Enter Serial Number :")   ## Fonction pour créer un Qrcode par eleves avec un numéro de série
    img = qrcode.make(matricule)
    img.save("../public/assets/qrCode/"+matricule+"_"+firstname+"_"+lastname+"QR.png")


QrCreator(sys.argv[1],sys.argv[2],sys.argv[3])
# sudo apt install python3-pip
# pip3 install qrcode[pil]
# pip3 install opencv-python
# pip3 install cryptography