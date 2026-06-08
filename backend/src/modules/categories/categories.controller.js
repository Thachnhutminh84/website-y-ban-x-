const asyncHandler = require("../../utils/async-handler");
const service = require("./categories.service");

const listCategories = asyncHandler(async (req, res) => {
  const categories = await service.getCategories();
  res.json({ data: categories });
});

module.exports = {
  listCategories
};
