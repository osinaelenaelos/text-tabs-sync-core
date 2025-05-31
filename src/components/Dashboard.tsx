
import React from 'react';
import { Users, FileText, Activity, TrendingUp } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { UsersChart } from './UsersChart';
import { ActivityChart } from './ActivityChart';
import { RecentUsers } from './RecentUsers';

const Dashboard = () => {
  const stats = [
    {
      title: "Всего пользователей",
      value: "2,847",
      description: "+12% за месяц",
      icon: Users,
      color: "text-blue-600",
      bgColor: "bg-blue-50"
    },
    {
      title: "Подтвержденных",
      value: "2,341",
      description: "82% от общего числа",
      icon: Activity,
      color: "text-green-600",
      bgColor: "bg-green-50"
    },
    {
      title: "Текстовых страниц",
      value: "18,429",
      description: "+284 сегодня",
      icon: FileText,
      color: "text-purple-600",
      bgColor: "bg-purple-50"
    },
    {
      title: "Активность",
      value: "+23%",
      description: "За последнюю неделю",
      icon: TrendingUp,
      color: "text-orange-600",
      bgColor: "bg-orange-50"
    }
  ];

  return (
    <div className="space-y-8">
      {/* Header */}
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Dashboard</h1>
        <p className="text-muted-foreground">
          Обзор системы "Текстовые вкладки"
        </p>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {stats.map((stat, index) => {
          const Icon = stat.icon;
          return (
            <Card key={index} className="hover:shadow-lg transition-shadow duration-300">
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">
                  {stat.title}
                </CardTitle>
                <div className={`${stat.bgColor} p-2 rounded-lg`}>
                  <Icon className={`h-4 w-4 ${stat.color}`} />
                </div>
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{stat.value}</div>
                <p className="text-xs text-muted-foreground">
                  {stat.description}
                </p>
              </CardContent>
            </Card>
          );
        })}
      </div>

      {/* Charts Section */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Рост пользователей</CardTitle>
            <CardDescription>
              Динамика регистрации за последние 6 месяцев
            </CardDescription>
          </CardHeader>
          <CardContent>
            <UsersChart />
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Активность</CardTitle>
            <CardDescription>
              Создание и обновление страниц за неделю
            </CardDescription>
          </CardHeader>
          <CardContent>
            <ActivityChart />
          </CardContent>
        </Card>
      </div>

      {/* Recent Users */}
      <Card>
        <CardHeader>
          <CardTitle>Недавние регистрации</CardTitle>
          <CardDescription>
            Последние зарегистрированные пользователи
          </CardDescription>
        </CardHeader>
        <CardContent>
          <RecentUsers />
        </CardContent>
      </Card>
    </div>
  );
};

export default Dashboard;
