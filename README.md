BUAAWeChat
===============

W.I.P

### 简介

2.0 版本相对之前的版本是一个自底向上的完全重写。从 2.0 开始，正式引入 Module 模式，即公众账号的功能和内核完全脱离，每个功能都以一个单独的 Module 的形式存在并独立加载，不同功能模块之间完全解耦，极大地增强了系统的稳定性和可维护性。

2.0 版本的数据库也经过整合与重新设计，目前处于草稿阶段，详细说明：[Database Scheme Design](https://github.com/renfeisong/buaasoft-wechat/wiki/Database-Scheme-Design---Draft-1)

关于如何开发一个 Module，请参考 [Module PG](https://github.com/renfeisong/buaasoft-wechat/wiki/Module-Programming-Guide)

此外，2.0 还新增了许多方便的辅助工具，以便开发者可以快速开发 Module。目前包括 [WXDB] (https://github.com/renfeisong/buaasoft-wechat/wiki/WXDB-Class-Reference) 和 [OutputFormatter](https://github.com/renfeisong/buaasoft-wechat/wiki/OutputFormatter-Class-Reference)
