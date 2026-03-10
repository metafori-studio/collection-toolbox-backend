{
  description = "collection_toolbox_backend infrastructure modules";

  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixos-unstable";
    flake-utils.url = "github:numtide/flake-utils";
    metafori-infra.url = "git+ssh://git@github.com/metafori-studio/infra.git";
  };

  outputs =
    {
      nixpkgs,
      flake-utils,
      metafori-infra,
      ...
    }:
    flake-utils.lib.eachDefaultSystem (
      system:
      let
        pkgs = nixpkgs.legacyPackages.${system};
        metaforiInfra = metafori-infra.lib;
      in
      {
        devShells.default = pkgs.mkShell (
          metaforiInfra.devshell {
            inherit pkgs metaforiInfra;
            enableDatabases = true;
            enableMonitoring = true;
            enableStorage = true;
          }
        );

        packages.default = metaforiInfra.php { inherit pkgs; };
      }
    );
}
