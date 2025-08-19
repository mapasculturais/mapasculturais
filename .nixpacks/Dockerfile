FROM ghcr.io/railwayapp/nixpacks:ubuntu-1727136237

ENTRYPOINT ["/bin/bash", "-l", "-c"]
WORKDIR /app/


COPY .nixpacks/nixpkgs-dbc4f15b899ac77a8d408d8e0f89fa9c0c5f2b78.nix .nixpacks/nixpkgs-dbc4f15b899ac77a8d408d8e0f89fa9c0c5f2b78.nix
RUN nix-env -if .nixpacks/nixpkgs-dbc4f15b899ac77a8d408d8e0f89fa9c0c5f2b78.nix && nix-collect-garbage -d

COPY .nixpacks/assets /assets/
ARG NIXPACKS_METADATA NIXPACKS_PHP_FALLBACK_PATH NIXPACKS_PHP_ROOT_DIR PORT POST_MAX_SIZE UPLOAD_MAX_FILESIZE
ENV NIXPACKS_METADATA=$NIXPACKS_METADATA NIXPACKS_PHP_FALLBACK_PATH=$NIXPACKS_PHP_FALLBACK_PATH NIXPACKS_PHP_ROOT_DIR=$NIXPACKS_PHP_ROOT_DIR PORT=$PORT POST_MAX_SIZE=$POST_MAX_SIZE UPLOAD_MAX_FILESIZE=$UPLOAD_MAX_FILESIZE

# setup phase
COPY . /app/.
RUN PHP_INI_PATH=$(php --ini | grep "Additional .ini files parsed:" | cut -d: -f2 | tr -d " "); echo "PHP_INI_PATH is $PHP_INI_PATH"; echo "upload_max_filesize=128M" >> $PHP_INI_PATH; echo "post_max_size=128M" >> $PHP_INI_PATH; echo "Content of php.ini below:"; cat "$PHP_INI_PATH"

# install phase
COPY . /app/.
RUN  mkdir -p /var/log/nginx && mkdir -p /var/cache/nginx
RUN  composer install --ignore-platform-reqs
RUN  pnpm i --frozen-lockfile

# build phase
COPY . /app/.
RUN  pnpm run build





# start
COPY . /app
CMD ["node /assets/scripts/prestart.mjs /assets/nginx.template.conf /nginx.conf && (php-fpm -y /assets/php-fpm.conf & nginx -c /nginx.conf)"]

