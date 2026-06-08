const asyncHandler = require("../../utils/async-handler");
const service = require("./news.service");

const listNews = asyncHandler(async (req, res) => {
  const news = await service.listNews({
    category: req.query.category
  });

  res.json({ data: news });
});

const getNews = asyncHandler(async (req, res) => {
  const article = await service.getNews(req.params.identifier);
  res.json({ data: article });
});

const createNews = asyncHandler(async (req, res) => {
  const article = await service.createNews(req.body, req.file);
  res.status(201).json({ data: article, message: "News created" });
});

const updateNews = asyncHandler(async (req, res) => {
  const article = await service.updateNews(Number(req.params.id), req.body, req.file);
  res.json({ data: article, message: "News updated" });
});

const deleteNews = asyncHandler(async (req, res) => {
  await service.removeNews(Number(req.params.id));
  res.status(204).send();
});

module.exports = {
  listNews,
  getNews,
  createNews,
  updateNews,
  deleteNews
};
