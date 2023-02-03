# What is Checking System ?

Checking system is an advanced attendance system for companies or schools, students/employees can scan a QRCode to sign in and the system records the present in the database. The system consists of a website accessible by teachers/employers or they can see the presence of students/employees.
Teachers/employers can also add courses/meetings in the calendars of students/employees.
Each time a student/employee scans a QRCode, in addition to signing in, the system sends by mail to the student/employee his schedule for the week which is updated by the teachers/employers.

# How to start our project

### Step 1 : clone the project

With SSH
```bash 
$ git clone git@rendu-git.etna-alternance.net:module-8832/activity-50388/group-996100.git
```

With HTTPS
```bash
$ git clone https://rendu-git.etna-alternance.net/module-8832/activity-50388/group-996100.git
```

### Step 2 : install the dependencies

For install symfony 6 dependencies, you need to follow this [link](https://aymeric-cucherousset.fr/installer-symfony-6-sur-debian-11/)

Verify that you have installed the dependencies with the following command

```bash 
$ symfony check:requirements 
```

Now you can install the project dependencies

```bash
$ composer install
```

Then you need to install Python3 and dependencies for python3 script

```bash
$ sudo apt install python3 python3-pip
$ pip3 install qrcode[pil]
$ pip3 install opencv-python
$ pip3 install cryptography
$ pip3 install mysql-connector-python==8.0.28
```

### Step 3 : If you want a personal database create it
First, you need to create the database

```bash
$ php bin/console doctrine:database:create
```

After this, you need to configure the .env file with your database credentials

Example :

```bash
DATABASE_URL="mysql://username:password@localhost:port/dbname?serverVersion=mariadb-10.5.15"
```
---

## In our project, we use a cloud database, so you need to configure your project as you want with the documentation [here](https://symfony.com/doc/current/doctrine.html)

---
### Step 4 : migrate the database

```bash
$  php bin/console make:migration
```

```bash
$ php bin/console doctrine:migrations:migrate
```


### Step 5 : administate the database with TablePlus

For create the first user, you need to install TablePlus. You can download it [here](https://tableplus.com/download)

then you need to connect to your database with the following credentials
    
```bash 
    name : what you want
    Host : localhost or 127.0. 0. 1
    Port : 3306
    Username : Your username
    Password : Your password
    Database : Your database name
```
Once you are connected, you can create the first user in the table `user` with the keyboards `CTRL + i` enter data you want and set **`admin`** to "1" for the first user

### Step 5.1 : create the default Calendar for avoid errors

Again in TablePlus, you need to create a default calendar in the table `calendar` with the keyboards `CTRL + i` enter data you want and let or set **`id`** to "0" for the first calendar

### Step 6 : run the project

```bash
$ symfony serve
```
Then you can access to the project onclick on the link that symfony serve give you in the terminal

---

### Step 7 : Install the python dependencies for scanning QRCode

```bash
$ 
```