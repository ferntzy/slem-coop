import { Calendar } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Badge as UIBadge } from '../components/ui/badge';
import { useEffect, useState } from 'react';

export function Promos() {
  const [heroVisible, setHeroVisible] = useState(false);

  useEffect(() => {
    setHeroVisible(true);
  }, []);

  const promos = [
    {
      title: 'New Member Welcome Bonus',
      description: 'Get ₱50 bonus credit when you open your first savings account',
      validity: 'Valid until March 31, 2026',
      image: 'https://images.unsplash.com/photo-1607083206869-4c7672e72a8a?w=800'
    },
    {
      title: 'Zero Interest Promo',
      description: '0% interest on personal loans up to ₱5,000 for the first 6 months',
      validity: 'Limited slots available',
      image: 'https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?w=800'
    },
    {
      title: 'Refer a Friend',
      description: 'Earn ₱100 for every successful referral who becomes a member',
      validity: 'Ongoing promotion',
      image: 'https://images.unsplash.com/photo-1521791136064-7986c2920216?w=800'
    },
  ];

  return (
    <div className="flex flex-col">
      <section className="relative bg-gradient-to-br from-primary to-secondary text-white py-16 sm:py-24 overflow-hidden">
        <div
          className="absolute inset-0 bg-[url('/src/images/bghd.jpg')] bg-cover bg-center"
          style={{ transition: 'transform 20s linear', transform: heroVisible ? 'scale(1)' : 'scale(1.05)' }}
        />
        <div className="absolute inset-0 bg-primary/60" />
        <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <UIBadge className="mb-4 bg-white/20 text-white border-white/30">Special Offers</UIBadge>
          <h1 className="text-4xl sm:text-5xl font-bold mb-4">Promos & Offers</h1>
          <p className="text-lg text-blue-100">
            Exclusive deals and promotions for our valued members
          </p>
        </div>
      </section>

      <section className="py-16">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {promos.map((promo, index) => (
              <Card key={index} className="rounded-2xl border-border/50 overflow-hidden hover:shadow-xl transition-all">
                <div className="aspect-video overflow-hidden bg-muted">
                  <img src={promo.image} alt={promo.title} className="w-full h-full object-cover" />
                </div>
                <CardHeader>
                  <UIBadge className="w-fit mb-2 bg-gradient-to-r from-primary to-secondary text-white">Active</UIBadge>
                  <CardTitle>{promo.title}</CardTitle>
                </CardHeader>
                <CardContent>
                  <p className="text-sm text-muted-foreground mb-2">{promo.description}</p>
                  <div className="flex items-center gap-2 text-xs text-muted-foreground">
                    <Calendar className="w-3 h-3" />
                    {promo.validity}
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </section>
    </div>
  );
}

function HeroSection({ title, subtitle }: { title: string; subtitle: string }) {
  const [heroVisible, setHeroVisible] = useState(false);
  useEffect(() => { setHeroVisible(true); }, []);
  return (
    <section className="relative bg-gradient-to-br from-primary to-secondary text-white py-16 sm:py-24 overflow-hidden">
      <div
        className="absolute inset-0 bg-[url('/src/images/bghd.jpg')] bg-cover bg-center"
        style={{ transition: 'transform 20s linear', transform: heroVisible ? 'scale(1)' : 'scale(1.05)' }}
      />
      <div className="absolute inset-0 bg-primary/60" />
      <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 className="text-4xl font-bold mb-4">{title}</h1>
        <p className="text-lg text-blue-100">{subtitle}</p>
      </div>
    </section>
  );
}


