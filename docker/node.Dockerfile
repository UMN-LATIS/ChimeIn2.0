FROM node:10.16.3-stretch as frontend1
WORKDIR /app
RUN mkdir -p /app/public
COPY package-lock.json package.json webpack.mix.js /app/
RUN npm install
COPY .babelrc /app
COPY artisan /app
EXPOSE 8080

CMD ["npm","run","hot"]