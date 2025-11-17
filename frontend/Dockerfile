FROM debian:bullseye-slim

RUN apt-get update && apt-get install -y \
    curl \
    git \
    vim \
    nano \
    build-essential \
    ca-certificates \
    --no-install-recommends && \
    rm -rf /var/lib/apt/lists/*


ENV NVM_DIR /usr/local/nvm
ENV NODE_VERSION 18.13.0
RUN mkdir -p $NVM_DIR && \
    curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.1/install.sh | bash && \
    . $NVM_DIR/nvm.sh && \
    nvm install $NODE_VERSION && \
    nvm alias default $NODE_VERSION && \
    nvm use default && \
    npm install -g @angular/cli@15.2.9

ENV NODE_PATH $NVM_DIR/versions/node/v$NODE_VERSION/lib/node_modules
ENV PATH $NVM_DIR/versions/node/v$NODE_VERSION/bin:$PATH

WORKDIR /usr/src/tidyup

EXPOSE 4200

CMD ["ng", "serve", "--host", "0.0.0.0", "--disable-host-check"]

