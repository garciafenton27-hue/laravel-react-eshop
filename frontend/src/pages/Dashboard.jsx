import { useEffect, useState } from 'react';
import api from '../services/api';

const Dashboard = () => {
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        api.get('/orders')
            .then(res => setOrders(res.data.data))
            .catch(err => console.error(err))
            .finally(() => setLoading(false));
    }, []);

    if (loading) return <div className="p-10 text-center">Loading dashboard...</div>;

    return (
        <div className="max-w-4xl mx-auto bg-white p-6 shadow-md rounded">
            <h1 className="text-2xl font-bold mb-6">My Orders</h1>
            {orders.length === 0 ? (
                <p>No orders found.</p>
            ) : (
                <div className="space-y-6">
                    {orders.map(order => (
                        <div key={order.id} className="border rounded p-4">
                            <div className="flex justify-between mb-4 bg-gray-50 p-2 rounded">
                                <div>
                                    <span className="font-bold">Order #{order.id}</span>
                                    <span className="text-gray-500 text-sm ml-2">
                                        {new Date(order.created_at).toLocaleDateString()}
                                    </span>
                                </div>
                                <div className="text-right">
                                    <div className="font-bold">₹{order.total_amount}</div>
                                    <div className={`text-sm ${order.status === 'delivered' ? 'text-green-600' : 'text-orange-600'}`}>
                                        {order.status.toUpperCase()}
                                    </div>
                                    <div className={`text-xs ${order.payment_status === 'paid' ? 'text-green-600' : 'text-red-500'}`}>
                                        Payment: {order.payment_status.toUpperCase()}
                                    </div>
                                </div>
                            </div>
                            <div className="space-y-2">
                                {order.items.map(item => (
                                    <div key={item.id} className="flex justify-between text-sm">
                                        <span>{item.product.name} x {item.quantity}</span>
                                        <span>₹{item.price * item.quantity}</span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

export default Dashboard;
