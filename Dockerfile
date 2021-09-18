FROM node:latest as build-stage

COPY ./client /home/node/app
WORKDIR /home/node/app

RUN npm install
RUN npm run build

FROM nginx:latest

# COPY ./client/public /usr/share/nginx/html
COPY ./config/nginx.conf /etc/nginx/nginx.conf

COPY --from=build-stage /home/node/app/public /usr/share/nginx/html
