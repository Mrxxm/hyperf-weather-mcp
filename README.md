# Hyperf Weather MCP

本机天气查询 PoC：使用 Hyperf 同时提供 REST 和 MCP Streamable HTTP 接口，供后续 Codex 接入。

## 启动

```bash
cd /Users/xiaomeng/Documents/zw/hyperf-weather-mcp
docker compose up -d --build
```

服务只绑定本机 `127.0.0.1:9601`。

## REST 验证

```bash
curl --get 'http://127.0.0.1:9601/weather' \
  --data-urlencode 'city=上海'
```

## MCP

- URL：`http://127.0.0.1:9601/mcp`
- Transport：Streamable HTTP
- Tool：`get_weather`
- 参数：`{"city":"上海"}`

使用 MCP Inspector：

```bash
npx @modelcontextprotocol/inspector@latest
```

后续注册到 Codex：

```bash
codex mcp add hyperf-weather --url http://127.0.0.1:9601/mcp
codex mcp list
```

## 测试与停止

```bash
docker compose exec app composer test
docker compose down
```

完整设计、准备项和验收清单见 [技术方案](docs/技术方案.md)。
