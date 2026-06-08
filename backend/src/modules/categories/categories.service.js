const repository = require("./categories.repository");

async function getCategories() {
  return repository.listActiveCategories();
}

module.exports = {
  getCategories
};
