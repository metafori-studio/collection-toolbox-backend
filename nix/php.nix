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
      memory_limit = 256M
      upload_max_filesize = 20M
      post_max_size = 20M
    '';
  };
in
php
