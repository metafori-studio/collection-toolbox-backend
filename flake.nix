{
  description = "PHP 8.5 development environment with PostgreSQL 18 and Valkey";

  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixos-unstable";
    flake-utils.url = "github:numtide/flake-utils";
  };

  outputs =
    {
      self,
      nixpkgs,
      flake-utils,
    }:
    flake-utils.lib.eachDefaultSystem (
      system:
      let
        pkgs = nixpkgs.legacyPackages.${system};

        config = import ./nix/config.nix;
        php = import ./nix/php.nix { inherit pkgs; };
        databases = import ./nix/databases.nix { inherit pkgs config; };

      in
      {
        devShells.default = pkgs.mkShell {
          buildInputs = [
            php
            pkgs.php85Packages.composer
            databases.pkgs_postgresql
            databases.pkgs_valkey
            databases.start
            databases.stop
          ];

          shellHook = ''
            echo "PHP 8.5 development environment with PostgreSQL 18 and Valkey"
            echo ""
            echo "PHP version: $(php --version | head -n 1)"
            echo "PostgreSQL version: $(${databases.pkgs_postgresql}/bin/postgres --version)"
            echo "Valkey version: $(${databases.pkgs_valkey}/bin/valkey-server --version | head -n 1)"
            echo ""
            echo "Enabled PHP extensions:"
            php -m | grep -E "(pdo_pgsql|pgsql|redis)"
            echo ""

            # Start databases automatically
            ${databases.start}/bin/start-databases

            # Set up trap to stop databases.on exit
            trap "${databases.stop}/bin/stop-databases" EXIT

            echo ""
            echo "Data is stored in ./${config.dataDir}/"
            echo "databases will stop automatically when you exit this shell."
          '';
        };

        packages.default = php;
      }
    );
}
