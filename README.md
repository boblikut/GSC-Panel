# GSC - Panel

### Project designed by [Vantuzz](https://steamcommunity.com/id/vantuzz_nya/)

## Intro
This is an administration panel for Garry's Mod servers. Your administrators will be able to perform their duties, even without the ability to log into the Garry's Mod server, via a convenient web interface.

## About the Project
The project is a self-hosted web application + scripts for Garry's Mod. This means you will have to host this project on your own hosting or by other means.

## Usage
First of all, you need to log into the admin account somehow. It's simple to do. By default, the following account is already created in the database:

Login: `owner`
Password: `1234`

Immediately after logging into the account, change your password. To do this, go to the Admin Panel, users, and click on pencil near of "owner" login. You can also change the login there.

To create accounts for your admins, you can click on Create Account in the users tab.

To edit roles and create new ones, go to the Roles tab and do it. Also you can create own player's commands. Check `gsc/plugins/commands_example.lua` for examples. Don't remember sync roles rights with player commands at Admin Panel -> Users -> Sync Rights

Also you can make that admins could check custom info about players. To do it check `gsc/plugins/info_example.lua` with examples.

## Installation
0. Download `Docker`
1. Launch `Docker`
2. Download docker image by typing this command: `docker pull ghcr.io/boblikut/gsc-panel:latest`
3. Transfer folder `gsc` from `addons` to your `addons` folder on your gmod server
4. Open `gsc/sv_gsc-panel.lua` and change `ws://localhost:80/ws` on actual websocket adress(`ws://[DOMEN or IP]:[PORT]/ws` (80 port - default))
5. Download [GWSockets](https://github.com/FredyH/GWSockets) and put .dll from realeses that you need to `lua/bin`

## Launch
1. Create and launch docker container thx this command: `docker run --name gsc-panel -d --restart always -p 80:80 -v gsc-db-volume:/var/www/html/db ghcr.io/boblikut/gsc-panel:latest`. If port 80 is not avalible - change it. For example on 67. Example: -p 67:80
2. To restart container type: `docker restart gsc-panel`
3. To stop created container: `docker stop gsc-panel`
4. To start created container: `docker start gsc-panel`
5. Generate a token at Admin -> Token and put it at `data/gsc/gsc-token.txt` of your gmod server (need after first launching)

*In linux write `sudo` before `docker`*

If you installed the addon correctly and the server is running, you will immediately see up-to-date statistics, players, etc.

## Screenshots

<img width="964" height="457" alt="image" src="https://github.com/user-attachments/assets/bdb64a5c-4c04-456b-8d66-0fbee88c1fcc" />

<img width="889" height="427" alt="image" src="https://github.com/user-attachments/assets/b0b75880-4db6-44d9-a7c8-2f13997484d8" />

<img width="960" height="457" alt="image" src="https://github.com/user-attachments/assets/72931490-2d16-41f2-8b41-c161c4dfa2d0" />

<img width="547" height="693" alt="image" src="https://github.com/user-attachments/assets/d8b571ff-8d58-4892-aa6f-6419f4a604b7" />


## Afterword
If you have any problems with the project, suggestions, wishes, etc., then write to me on Discord (`boblikut`). If you will use this system you can write and I will add you to `Servers Already Using GSC-Panel`

## Plugins
Plugins are avalible by this link: https://github.com/boblikut/GSC-Panel-plugins
If you will make smth plugin for any addon you can add it to plugins repo thx Pull Request
To dowload plugin just transfer lua file to `gsc/plugins` folder

## Acknowledgments

* [TrafeX](https://github.com/TrafeX) - cool [docker image](https://github.com/TrafeX/docker-php-nginx) for multiplatform PHP apps
* [Unknown Developer](https://github.com/unknown-gd) - help with Docker
* [Winkarst](https://github.com/Winkarst-cpu) - testing

## Servers Already Using GSC-Panel
* No one yet) Be the first!
