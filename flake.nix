{
  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixos-unstable";
    flake-utils.url = "github:numtide/flake-utils";
    infra.url = "git+ssh://git@github.com/metafori-studio/infra.git?dir=nix";
  };

  outputs =
    {
      nixpkgs,
      flake-utils,
      infra,
      ...
    }:
    flake-utils.lib.eachDefaultSystem (
      system:
      let
        pkgs = nixpkgs.legacyPackages.${system};
        metafori = infra.lib;
      in
      {
        devShells.default = pkgs.mkShell (
          metafori.devshell {
            inherit pkgs metafori;
            enableDatabases = true;
            enableMonitoring = true;
            enableStorage = true;
            enableXdebug = false;
            configOverrides = {
              postgresDb = "collection_toolbox_backend";
            };
          }
        );

        packages.default = metafori.php { inherit pkgs; };
      }
    );
}
