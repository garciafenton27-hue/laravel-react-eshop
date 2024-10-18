import { useEffect, useState } from 'react';
import api from '../services/api';
import ProductCard from '../components/ProductCard';

const Home = () => {
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        api.get('/products')
            .then(res => {
                setProducts(res.data.data.data || res.data.data); // Handle pagination or plain list
            })
            .catch(err => console.error(err))
            .finally(() => setLoading(false));
    }, []);

    if (loading) return <div className="text-center py-10">Loading products...</div>;

    return (
        <div>
            <div className="mb-8 text-center">
                <h1 className="text-4xl font-bold text-gray-800 mb-4">Welcome to OpenShop</h1>
                <p className="text-gray-600">Premium products at open source prices.</p>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                {products.length > 0 ? (
                    products.map(product => (
                        <ProductCard key={product.id} product={product} />
                    ))
                ) : (
                    <div className="col-span-full text-center text-gray-500">No products found.</div>
                )}
            </div>
        </div>
    );
};

export default Home;
