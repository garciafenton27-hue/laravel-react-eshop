import { Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import api from '../services/api';
import { FaShoppingCart } from 'react-icons/fa';

const ProductCard = ({ product }) => {
    const { user } = useAuth();

    const addToCart = async (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (!user) {
            alert('Please login to add to cart');
            return;
        }
        try {
            await api.post('/cart/add', { product_id: product.id, quantity: 1 });
            alert('Added to cart!');
        } catch (error) {
            alert(error.response?.data?.message || 'Failed to add to cart');
        }
    };

    return (
        <div className="bg-white border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition">
            <Link to={`/products/${product.id}`}>
                <div className="h-48 bg-gray-200 flex items-center justify-center">
                    {product.images && product.images.length > 0 ? (
                        <img
                            src={product.images[0].image_path}
                            alt={product.name}
                            className="h-full w-full object-cover"
                            onError={(e) => {
                                e.target.src = '/placeholder.jpg';
                            }}
                        />
                    ) : (
                        <img
                            src="/placeholder.jpg"
                            alt="No product image"
                            className="h-full w-full object-cover"
                        />
                    )}
                </div>
                <div className="p-4">
                    <h3 className="text-lg font-semibold text-gray-800 truncate">{product.name}</h3>
                    <p className="text-gray-600 mt-1">${product.price}</p>
                    <button
                        onClick={addToCart}
                        className="mt-3 w-full flex items-center justify-center bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition"
                    >
                        <FaShoppingCart className="mr-2" /> Add to Cart
                    </button>
                </div>
            </Link>
        </div>
    );
};

export default ProductCard;
