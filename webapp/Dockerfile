FROM node:latest

RUN npm i -g npm3 && npm3 -g uninstall npm
RUN npm3 i -g npm@5.0.3
RUN npm -v

ENV HOST 0.0.0.0

ENV APP_DIR /app

VOLUME $APP_DIR
WORKDIR /


EXPOSE 3000

COPY run.sh /run.sh
RUN chmod 755 /run.sh


ENTRYPOINT [ "/run.sh"]
CMD ["dev" , "install"]
