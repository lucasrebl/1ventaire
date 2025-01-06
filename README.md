## installation


créer un fichier .env.local et mettre cette ligne dedans :
DATABASE_URL="mysql://1ventaire_user:1ventaire_password@database:3306/1ventaire_db?serverVersion=8.0.32&charset=utf8mb4"


docker-compose up -d

docker exec -it symfony_mysql mysql -u root -p

mdp : 1ventaire_root_password

pour créer l'user mysql

CREATE USER '1ventaire_user'@'%' IDENTIFIED BY '1ventaire_password';
GRANT ALL PRIVILEGES ON symfony_db.* TO '1ventaire_user'@'%';
FLUSH PRIVILEGES;

SHOW DATABASES;

exit


pour lancer le server

php -S 127.0.0.1:8000 -t public
