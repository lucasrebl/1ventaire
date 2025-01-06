## Installation


### Créer un fichier .env.local et mettre cette ligne dedans :
```dotenv
DATABASE_URL="mysql://1ventaire_user:1ventaire_password@database:3306/1ventaire_db?serverVersion=8.0.32&charset=utf8mb4"
```

### Créer les containers docker
```bash
docker-compose up -d
```
### Pour créer l'user mysql
#### Se connecter au container mysql
```bash
docker exec -it 1ventaire_mysql mysql -u root -p 
```
(Le mot de passe est "1ventaire_root_password")
#### Créer l'user
```mysql
CREATE USER '1ventaire_user'@'%' IDENTIFIED BY '1ventaire_password';
GRANT ALL PRIVILEGES ON 1ventaire_db.* TO '1ventaire_user'@'%';
FLUSH PRIVILEGES;
```
Pour voir les bases de données :
```mysql
SHOW DATABASES;
```

http://localhost:8000
