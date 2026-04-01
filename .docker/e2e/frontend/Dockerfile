# Stage 1 — build the AngularJS SPA
# Uses the same Node 10 + Ruby/Compass combination as the development Docker.
FROM debian:bullseye-slim AS builder

RUN apt-get update -q \
 && apt-get install -y -q --no-install-recommends \
      ca-certificates curl git unzip ruby-dev build-essential \
 && mkdir /opt/nodejs \
 && curl -sL https://nodejs.org/download/release/v10.24.1/node-v10.24.1-linux-x64.tar.gz \
    | tar -xz --strip-components=1 -C /opt/nodejs \
 && ln -s /opt/nodejs/bin/node /usr/bin/node \
 && ln -s /opt/nodejs/bin/npm /usr/bin/npm \
 && gem install compass \
 && npm install -g bower grunt-cli \
 && rm -rf /var/lib/apt/lists/*

ENV PATH="/opt/nodejs/bin/:${PATH}"

WORKDIR /build/frontend
COPY frontend/package.json frontend/bower.json frontend/.bowerrc ./
RUN npm install
RUN bower install --allow-root

COPY frontend/ ./
# Point the API constant to /api — nginx (stage 2) proxies it to the api container.
RUN sed 's|http://glpiplugindirectory/api|/api|' app/scripts/conf.example.js \
      > app/scripts/conf.js \
 && grunt build --force

# Stage 2 — serve dist/ with nginx + proxy /api to the PHP backend
FROM nginx:1.25-alpine
COPY --from=builder /build/frontend/dist /usr/share/nginx/html
COPY .docker/e2e/frontend/nginx.conf /etc/nginx/conf.d/default.conf
