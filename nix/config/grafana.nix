{ config }:
{
  ini = ''
    [server]
    http_port = ${config.grafanaPort}
    domain = localhost

    [database]
    type = sqlite3
    path = grafana.db
    wal = false

    [users]
    allow_sign_up = false
    default_theme = dark

    [auth.anonymous]
    enabled = true
    org_name = Main Org
    org_role = Admin
  '';

  datasources = ''
    apiVersion: 1
    datasources:
      - name: "prometheus"
        type: prometheus
        access: proxy
        url: http://127.0.0.1:${config.prometheusPort}
        uid: "prometheus"
        isDefault: true
        editable: false
      - name: "tempo"
        type: tempo
        access: proxy
        url: http://127.0.0.1:${config.tempoPort}
        uid: "tempo"
        editable: false
      - name: "loki"
        type: loki
        access: proxy
        url: http://127.0.0.1:${config.lokiPort}
        uid: "loki"
        editable: false
  '';
}
