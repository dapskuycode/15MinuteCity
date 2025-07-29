// contoh di file React
import { useEffect, useState } from 'react';
import api from './Api';

function CategoryList() {
  const [categories, setCategories] = useState([]);

  useEffect(() => {
    api.get('/categories')
      .then((res) => {
        setCategories(res.data);
      })
      .catch((err) => {
        console.error('Gagal fetch kategori:', err);
      });
  }, []);

  return (
    <div>
      <h2>Kategori:</h2>
      <ul>
        {categories.map(cat => (
          <li key={cat.id}>{cat.name}</li>
        ))}
      </ul>
    </div>
  );
}

export default CategoryList;
