{ pkgs, ... }:
let
  php = pkgs.php85.buildEnv {
    extensions =
      {
        enabled,
        all,
      }:
      enabled
      ++ (with all; [
        pdo_pgsql
        pgsql
        redis
        imagick
        opentelemetry
      ]);
    extraConfig = ''
      memory_limit = 1024M
      upload_max_filesize = 500M
      post_max_size = 200M
    '';
  };
in
php
